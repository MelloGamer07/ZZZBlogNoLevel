<?php
class CommentValidator
{
    public const MAX_LENGTH = 400;
    public const MIN_LENGTH = 1;

    private const LEET_MAP = [
        '@' => 'a', '4' => 'a',
        '8' => 'b',
        '(' => 'c',
        '3' => 'e', '£' => 'e',
        '6' => 'g',
        '#' => 'h',
        '!' => 'i', '1' => 'i', '|' => 'i',
        '0' => 'o',
        '$' => 's', '5' => 's',
        '+' => 't', '7' => 't',
        '%' => 'x',
        '2' => 'z',
    ];

    private const FALLBACK_BANNED_WORDS = [
        'suicide',
        'suicidal',
    ];

    protected array $bannedWords;
    protected ?mysqli $conn;

    public function __construct(?mysqli $conn = null)
    {
        $this->conn        = $conn;
        $this->bannedWords = $this->loadBannedWords();
    }

    public function validate(string $raw): array
    {
        $comment = trim($raw);

        if (strlen($comment) < self::MIN_LENGTH) {
            return $this->fail("Il commento non può essere vuoto.");
        }

        if (strlen($comment) > self::MAX_LENGTH) {
            return $this->fail(
                "Il commento supera il limite di " . self::MAX_LENGTH . " caratteri."
            );
        }

        $normalised = $this->normalise($comment);

        foreach ($this->bannedWords as $word) {
            if ($this->matchesBannedWord($normalised, $word)) {
                return $this->fail("Il tuo commento contiene un linguaggio inappropriato.");
            }
        }

        return ['ok' => true, 'error' => null];
    }

    public function isClean(string $raw): bool
    {
        return $this->validate($raw)['ok'];
    }

    public function normalise(string $text): string
    {
        $t = mb_strtolower($text, 'UTF-8');

        $t = strtr($t, self::LEET_MAP);

        $prev = '';
        while ($prev !== $t) {
            $prev = $t;
            $t    = preg_replace('/([a-z0-9])[^a-z0-9 ]+([a-z0-9])/u', '$1$2', $t);
        }

        $t = preg_replace('/([a-z])\1+/u', '$1', $t);

        $t = preg_replace_callback(
            '/(?<![a-z])([a-z](?: [a-z]){2,})(?![a-z])/u',
            static fn($m) => str_replace(' ', '', $m[1]),
            $t
        );

        return trim(preg_replace('/\s+/', ' ', $t));
    }

    private function matchesBannedWord(string $normalisedText, string $bannedWord): bool
    {
        $normRoot = $this->normalise($bannedWord);
        if ($normRoot === '') {
            return false;
        }

        return (bool) preg_match(
            '/\b' . preg_quote($normRoot, '/') . '\b/u',
            $normalisedText
        );
    }
    protected function loadBannedWords(): array
    {
        $words = self::FALLBACK_BANNED_WORDS;

        if ($this->conn !== null) {
            $result = $this->conn->query("SELECT Parola FROM ParoleBan ORDER BY Id");
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $words[] = mb_strtolower(trim($row['Parola']), 'UTF-8');
                }
                $result->free();
            }
        }

        return array_values(array_unique(array_filter($words)));
    }

    private function fail(string $message): array
    {
        return ['ok' => false, 'error' => $message];
    }
}