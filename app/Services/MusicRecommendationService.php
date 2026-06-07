<?php

namespace App\Services;

use App\Models\Scan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Prism\Prism\Enums\Provider;
use Prism\Prism\Facades\Prism;
use Prism\Prism\Schema\ArraySchema;
use Prism\Prism\Schema\BooleanSchema;
use Prism\Prism\Schema\NumberSchema;
use Prism\Prism\Schema\ObjectSchema;
use Prism\Prism\Schema\StringSchema;

class MusicRecommendationService
{
    /**
     * Ask the configured AI model for music recommendations matching the given scan.
     *
     * @return array{prompt: string, result_source: string, structured: array<string, mixed>|null}
     */
    public function recommend(Scan $scan): array
    {
        $model = config('services.openai.model');
        $prompt = $this->buildPrompt($scan);

        $response = Prism::structured()
            ->using(Provider::OpenAI, $model)
            ->withSchema($this->schema())
            ->withPrompt($prompt)
            ->asStructured();

        return [
            'prompt' => $prompt,
            'result_source' => "openai/{$model}",
            'structured' => $this->withVerifiedLinks($response->structured),
        ];
    }

    /**
     * Replace each suggestion's link with a link to a real, existing track.
     *
     * The model invents plausible-sounding track/creator pairings it cannot
     * verify, so its own "link" is liable to be a dead 404 and a site search
     * for that exact (often fictional) pairing turns up nothing. Instead we
     * look the suggestion up against Deezer's open catalogue (no API key
     * required): if the suggested track really exists we link straight to it;
     * otherwise we fall back to a real track in the same genre, so the user
     * always lands on an actual, playable song rather than an empty results page.
     *
     * @param  array<string, mixed>|null  $structured
     * @return array<string, mixed>|null
     */
    private function withVerifiedLinks(?array $structured): ?array
    {
        if (! is_array($structured['music_suggestions'] ?? null)) {
            return $structured;
        }

        foreach ($structured['music_suggestions'] as &$suggestion) {
            if (! is_array($suggestion)) {
                continue;
            }

            $trackName = (string) ($suggestion['track_name'] ?? '');
            $creator = (string) ($suggestion['creator'] ?? '');
            $platform = (string) ($suggestion['source_platform'] ?? '');
            $genre = $this->primaryGenre((string) ($suggestion['style_or_genre'] ?? ''));
            $themes = array_values(array_filter(array_map(
                fn ($theme) => trim((string) $theme),
                is_array($suggestion['themes_matched'] ?? null) ? $suggestion['themes_matched'] : [],
            )));

            // Maybe the suggestion is a real track — search for it directly,
            // but only trust the result if it actually resembles what was
            // asked for. Deezer's search is fuzzy and "succeeds" even for
            // fictional names, returning some loosely-related guess that's
            // worse than a track picked for actually fitting the scene.
            $exact = $this->searchDeezer(trim("{$trackName} ".$this->primaryCreator($creator)));

            if ($exact !== null && ! $this->resembles($trackName, $creator, $exact)) {
                $exact = null;
            }

            // Otherwise, search by *what the scene is about*: pairs/singles of
            // matched themes (e.g. "pirates adventure", "treasure high seas")
            // are short, thematically on-point phrases that reliably surface
            // real tracks about the same thing. Mixing in genre words backfires
            // — long phrases go quiet, short ones collide with band names like
            // "The Cinematic Orchestra" / "Ambient Gate Music".
            $real = $exact
                ?? $this->searchDeezer(trim(($themes[0] ?? '').' '.($themes[1] ?? '')))
                ?? $this->searchDeezer($themes[0] ?? '')
                ?? $this->searchDeezer($genre)
                ?? $this->searchDeezer('royalty free music');

            // Deezer here is purely an oracle for "does a track like this
            // actually exist?" — not the destination. Send the listener to the
            // platform named in the suggestion, searching it for the verified
            // real title/artist (never the AI's possibly-fictional guess): a
            // search-results page for a real combo reliably surfaces the track
            // or something extremely close to it, one for a fictional combo
            // reliably comes back empty. This spreads links across YouTube,
            // SoundCloud, Bandcamp, Free Music Archive, Spotify, etc. instead
            // of funnelling everything through one service — *but* only when
            // $exact independently confirms the AI's pick is real: catalogues
            // barely overlap (a Deezer hit for "Cozy Rain" by Peppertune is no
            // guarantee Free Music Archive's much smaller library carries it
            // too), so a *substituted* track — one the oracle found by theme
            // when the AI's own guess turned out fictional — only has a
            // confirmed home on the oracle's own platform. Route those to
            // YouTube instead: its catalogue and search are broad/forgiving
            // enough to surface *something* relevant for almost any real combo.
            $title = $real['title'] ?? $trackName;
            $artist = $real['artist'] ?? $creator;
            $linkPlatform = $exact !== null ? $platform : '';

            $suggestion['link'] = $this->searchLinkFor($linkPlatform, $title, $artist)
                ?? $real['link']
                ?? 'https://www.deezer.com/search/'.urlencode(trim("{$title} {$artist}"));
        }

        return $structured;
    }

    /**
     * A search-results URL on the platform the AI named, querying for a
     * *verified real* title/artist. Each site's keyword search expects a
     * different query shape: YouTube, SoundCloud, Bandcamp, Spotify, and
     * Deezer all handle a combined "title artist" query well, but Free Music
     * Archive's narrow matcher often goes quiet the moment an artist name is
     * appended — title alone surfaces results that title+artist would miss.
     * Anything not recognised (vague strings like "Official artist site" or
     * "Official soundtrack purchase or streaming services" are common AI
     * guesses) falls through to YouTube, whose catalogue and search are broad
     * enough to surface *something* relevant for almost any real combo.
     */
    private function searchLinkFor(string $platform, string $title, string $artist): ?string
    {
        if (trim($title) === '') {
            return null;
        }

        $combined = urlencode(trim("{$title} {$artist}"));
        $titleOnly = urlencode($title);

        return match (true) {
            Str::contains($platform, ['free music archive', 'fma'], true) => "https://freemusicarchive.org/search?quicksearch={$titleOnly}",
            Str::contains($platform, 'soundcloud', true) => "https://soundcloud.com/search?q={$combined}",
            Str::contains($platform, 'bandcamp', true) => "https://bandcamp.com/search?q={$combined}",
            Str::contains($platform, 'spotify', true) => "https://open.spotify.com/search/{$combined}",
            Str::contains($platform, 'deezer', true) => "https://www.deezer.com/search/{$combined}",
            default => "https://www.youtube.com/results?search_query={$combined}",
        };
    }

    /**
     * Whether a Deezer result plausibly *is* the suggested track, rather than
     * just the closest thing Deezer's fuzzy search could find for a fictional
     * name. The *title* has to noticeably overlap — short of an exact match
     * (unrealistic given casing/punctuation/"(Instrumental)"-style suffixes),
     * but enough to rule out coincidences. A strong artist-name overlap alone
     * isn't sufficient: short, generic creator names like "Eliote" coincide
     * with real unrelated artists ("Mr Elliot") far more often than titles do,
     * and a right-artist/wrong-song result is more misleading than a themed pick.
     *
     * @param  array{title: string, artist: string, link: string}  $candidate
     */
    private function resembles(string $trackName, string $creator, array $candidate): bool
    {
        $titleSimilarity = $this->similarity($trackName, $candidate['title']);

        return $titleSimilarity >= 55.0
            || ($titleSimilarity >= 30.0 && $this->similarity($creator, $candidate['artist']) >= 60.0);
    }

    private function similarity(string $a, string $b): float
    {
        $a = Str::lower(trim($a));
        $b = Str::lower(trim($b));

        if ($a === '' || $b === '') {
            return 0.0;
        }

        similar_text($a, $b, $percent);

        return $percent;
    }

    /**
     * Look up a query against Deezer's keyless search API and return the
     * top real match — a track that is guaranteed to exist and load.
     *
     * @return array{title: string, artist: string, link: string}|null
     */
    private function searchDeezer(string $query): ?array
    {
        if (trim($query) === '') {
            return null;
        }

        try {
            $response = Http::timeout(6)->get('https://api.deezer.com/search', [
                'q' => $query,
                'limit' => 1,
            ]);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->ok()) {
            return null;
        }

        $track = $response->json('data.0');

        if (! is_array($track) || empty($track['link'])) {
            return null;
        }

        return [
            'title' => (string) ($track['title'] ?? ''),
            'artist' => (string) ($track['artist']['name'] ?? ''),
            'link' => (string) $track['link'],
        ];
    }

    /**
     * Site searches do plain keyword matching, not semantic search — long,
     * comma-and-slash-laden strings like "Pop/Electronic, cheerful melodies"
     * match nothing. A single short genre word (e.g. "Pop", "Ambient") is a
     * reliable fallback query when the suggested track itself can't be found.
     */
    private function primaryGenre(string $styleOrGenre): string
    {
        $primary = preg_split('/[\/,&]| feat\.? | ft\.? /i', $styleOrGenre)[0] ?? '';

        return trim($primary);
    }

    /**
     * The "creator" the AI names is often padded with descriptive trailing
     * text — "Hans Zimmer, from the Interstellar soundtrack", "Hans Zimmer
     * and others (from Pirates of the Caribbean: At World's End)" — that
     * pollutes a verification search and makes a genuinely real track look
     * fictional (Deezer can't find "Time Hans Zimmer, from the Interstellar
     * soundtrack" even though "Time" by "Hans Zimmer" is a real, exact hit).
     * Strip everything from the first such qualifier onward; collaboration
     * billings without one ("Macklemore & Ryan Lewis feat. Ray Dalton") are
     * left intact, since "&"/"feat." there are part of the real artist name.
     */
    private function primaryCreator(string $creator): string
    {
        $primary = preg_split('/,| and others\b| \(/i', $creator)[0] ?? '';

        return trim($primary);
    }

    private function buildPrompt(Scan $scan): string
    {
        $sceneDescription = $this->valueOrFallback($scan->description);
        $desiredFeeling = $this->valueOrFallback($scan->mood);
        $royaltyFreeOnly = $scan->royalty_free_only ? 'true' : 'false';
        $themes = $this->listOrFallback($scan->themes ?? []);

        return <<<PROMPT
            You are a music discovery assistant. Recommend music tracks that match a creative scene.

            Inputs
            - scene_description: {$sceneDescription}
            - desired_feeling: {$desiredFeeling}
            - royalty_free_only: {$royaltyFreeOnly}
            - themes: {$themes}

            Task
            Recommend 5-10 real, accessible tracks that best match the inputs. No follow-up questions — infer where inputs are vague.

            Rules
            1. Match scene description and emotional tone first.
            2. If royalty_free_only = true, restrict to Creative Commons, public domain, or royalty-free library tracks only.
            3. Only suggest tracks verifiably accessible online (YouTube Audio Library, Free Music Archive, Incompetech, SoundCloud, artist pages, etc.).
            4. Include accurate licensing info. If uncertain, flag it: "license_unconfirmed": true.
            5. Vary style across suggestions while staying on-brief.
            6. Never invent tracks. If a track is hypothetical, set "hypothetical": true — but prefer real tracks.
            PROMPT;
    }

    private function schema(): ObjectSchema
    {
        $suggestion = new ObjectSchema(
            name: 'music_suggestion',
            description: 'A single recommended track matching the creative scene.',
            properties: [
                new StringSchema('track_name', 'Title of the track.'),
                new StringSchema('creator', 'Artist, composer, or library that created the track.'),
                new StringSchema('style_or_genre', 'Musical style or genre of the track.'),
                new StringSchema('description', 'Why this track fits the scene and desired feeling.'),
                new ArraySchema(
                    name: 'themes_matched',
                    description: 'Themes from the input that this track matches.',
                    items: new StringSchema('theme', 'A matched theme.'),
                ),
                new StringSchema('license_type', 'License under which the track is available (e.g. Creative Commons BY, royalty-free library license, all rights reserved).'),
                new BooleanSchema('royalty_free', 'Whether the track is royalty-free / licensed for free use.'),
                new BooleanSchema('license_unconfirmed', 'True when the licensing details could not be verified.'),
                new BooleanSchema('hypothetical', 'True when the track is a plausible example rather than a verifiably real, existing track.'),
                new StringSchema('source_platform', 'Platform hosting the track (e.g. YouTube Audio Library, Free Music Archive, SoundCloud, artist site).'),
                new NumberSchema('estimated_bpm', 'Estimated tempo of the track in beats per minute.'),
                new StringSchema('instrumentation', 'Main instruments or sound sources featured in the track.'),
            ],
            requiredFields: [
                'track_name', 'creator', 'style_or_genre', 'description', 'themes_matched',
                'license_type', 'royalty_free', 'license_unconfirmed', 'hypothetical',
                'source_platform', 'estimated_bpm', 'instrumentation',
            ],
        );

        return new ObjectSchema(
            name: 'music_recommendations',
            description: 'Music recommendations for a creative scene, including an echo of the interpreted inputs.',
            properties: [
                new ObjectSchema(
                    name: 'input_summary',
                    description: "The assistant's interpretation of the supplied inputs.",
                    properties: [
                        new StringSchema('scene_description', 'The scene description as understood by the assistant.'),
                        new StringSchema('desired_feeling', 'The desired emotional tone as understood by the assistant.'),
                        new BooleanSchema('royalty_free_only', 'Whether results were restricted to royalty-free tracks.'),
                        new ArraySchema(
                            name: 'themes',
                            description: 'Themes considered for the recommendations.',
                            items: new StringSchema('theme', 'A theme.'),
                        ),
                    ],
                    requiredFields: ['scene_description', 'desired_feeling', 'royalty_free_only', 'themes'],
                ),
                new ArraySchema(
                    name: 'music_suggestions',
                    description: 'List of recommended tracks, ordered by how well they match the scene.',
                    items: $suggestion,
                    minItems: 5,
                    maxItems: 10,
                ),
            ],
            requiredFields: ['input_summary', 'music_suggestions'],
        );
    }

    private function valueOrFallback(?string $value): string
    {
        return filled($value) ? $value : 'not specified — infer from context';
    }

    /**
     * @param  array<int, string>  $items
     */
    private function listOrFallback(array $items): string
    {
        return $items === [] ? 'not specified — infer from context' : implode(', ', $items);
    }
}
