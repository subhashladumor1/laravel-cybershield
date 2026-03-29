<?php

namespace CyberShield\Security\Signatures;

use Illuminate\Support\Facades\File;

class SignatureLoader
{
    protected string $signaturePath;

    public function __construct(?string $path = null)
    {
        $this->signaturePath = $path ?? (string) shield_config('signatures.path', base_path('src/Signatures'));
    }

    /**
     * Load all signatures from the configured path.
     *
     * @return array<Signature>
     */
    public function loadAll(): array
    {
        if (!File::isDirectory($this->signaturePath)) {
            return [];
        }

        $files = File::files($this->signaturePath);
        $signatures = [];

        foreach ($files as $file) {
            if ($file->getExtension() === 'json') {
                $content = json_decode(File::get($file->getPathname()), true);
                if (is_array($content)) {
                    // Check if it's a list of signatures or a single signature
                    if (isset($content['id']) || isset($content['patterns'])) {
                        $signatures[] = Signature::fromArray($content);
                    } else {
                        foreach ($content as $item) {
                            $signatures[] = Signature::fromArray($item);
                        }
                    }
                }
            }
        }

        return $signatures;
    }

    /**
     * Load signatures by tag.
     */
    public function loadByTag(string $tag): array
    {
        return array_filter($this->loadAll(), function (Signature $sig) use ($tag) {
            return in_array($tag, $sig->tags);
        });
    }

    /**
     * Set the signature path.
     */
    public function setPath(string $path): void
    {
        $this->signaturePath = $path;
    }
}
