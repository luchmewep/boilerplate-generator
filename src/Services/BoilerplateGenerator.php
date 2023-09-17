<?php

namespace Luchavez\BoilerplateGenerator\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Luchavez\StarterKit\Services\StarterKit;
use Luchavez\StarterKit\Traits\HasTaggableCacheTrait;

/**
 * Class BoilerplateGenerator
 *
 * @author James Carlo Luchavez <jamescarloluchavez@gmail.com>
 */
class BoilerplateGenerator
{
    use HasTaggableCacheTrait;

    /**
     * @var ?string
     */
    protected ?string $author_name = null;

    /**
     * @var ?string
     */
    protected ?string $author_email = null;

    /**
     * @var ?string
     */
    protected ?string $author_homepage = null;

    /**
     * @return string
     */
    public function getMainTag(): string
    {
        return 'bg';
    }

    /***** CONFIG RELATED *****/

    /**
     * @return bool
     */
    public function isPestEnabled(): bool
    {
        return config('boilerplate-generator.pest_enabled');
    }

    /**
     * @return string
     */
    public function getAuthorName(): string
    {
        return $this->author_name ?? config('boilerplate-generator.author.name');
    }

    /**
     * @param  string|null  $author_name
     */
    public function setAuthorName(?string $author_name): void
    {
        $this->author_name = $author_name;
    }

    /**
     * @return string
     */
    public function getAuthorEmail(): string
    {
        return $this->author_email ?? config('boilerplate-generator.author.email');
    }

    /**
     * @param  string|null  $author_email
     */
    public function setAuthorEmail(?string $author_email): void
    {
        $this->author_email = $author_email;
    }

    /**
     * @return string
     */
    public function getAuthorHomepage(): string
    {
        return $this->author_homepage ?? config('boilerplate-generator.author.homepage');
    }

    /**
     * @param  string|null  $author_homepage
     */
    public function setAuthorHomepage(?string $author_homepage): void
    {
        $this->author_homepage = $author_homepage;
    }

    /**
     * @return string
     */
    public function getPackageSkeleton(): string
    {
        return config('boilerplate-generator.skeleton');
    }

    /***** PACKAGE RELATED *****/

    /**
     * @return Collection
     */
    public function getLocalPackages(): Collection
    {
        $path = str_replace('\\', '/', base_path('packages'));

        if (file_exists($path)) {
            return collect(File::directories($path))->map(function ($vendor_path) {
                $vendor_path = str_replace('\\', '/', $vendor_path);
                $vendor = Str::afterLast($vendor_path, '/');

                return collect(File::directories($vendor_path))->mapWithKeys(function ($package_path) use ($vendor) {
                    $package_path = str_replace('\\', '/', $package_path);
                    $package = Str::afterLast($package_path, '/');

                    return [$vendor.'/'.$package => $package_path];
                });
            })->collapse();
        }

        return collect();
    }

    /**
     * @param  string  $package
     * @return bool
     */
    public function isPackageLocal(string $package): bool
    {
        return $this->getLocalPackages()->has($package);
    }

    /**
     * Get all the packages installed with Package.
     *
     * @return Collection
     */
    public function getEnabledPackages(): Collection
    {
        $packages_path = base_path('packages/');
        $repositories = collect(getContentsFromComposerJson()->get('repositories', []));
        $pattern = '{'.addslashes($packages_path).'(.*)$}';

        return $repositories->mapWithKeys(function ($repository) use ($pattern) {
            if (isset($repository['url']) && preg_match($pattern, $repository['url'], $match)) {
                return [$match[1] => $match[0]];
            }

            return [];
        });
    }

    /**
     * @param  string  $package
     * @return bool
     */
    public function isPackageEnabled(string $package): bool
    {
        return $this->getEnabledPackages()->has($package);
    }

    /**
     * @param  bool  $with_details
     * @return Collection
     */
    public function getLoadedPackages(bool $with_details = false): Collection
    {
        return starterKit()->getPackages()->map(function ($package, $vendor_name) use ($with_details) {
            return collect($package)->mapWithKeys(function ($details, $package_name) use ($vendor_name, $with_details) {
                return [$vendor_name.'/'.$package_name => $with_details ? $details : $details['path']];
            });
        })
            ->collapse();
    }

    /**
     * @param  string  $package
     * @return bool
     */
    public function isPackageLoaded(string $package): bool
    {
        return $this->getLoadedPackages()->has($package);
    }

    /**
     * @param  string|array|null  $filter
     * @param  bool|null  $is_local
     * @param  bool|null  $is_enabled
     * @param  bool|null  $is_loaded
     * @param  bool  $with_details
     * @return Collection
     */
    public function getSummarizedPackages(string|array $filter = null, bool $is_local = null, bool $is_enabled = null, bool $is_loaded = null, bool $with_details = false): Collection
    {
        $loaded = $this->getLoadedPackages($with_details);
        $local = $this->getLocalPackages();
        $enabled = $this->getEnabledPackages();

        return $this->getMergedCollections($local, $loaded, $enabled, $filter, $is_local, $is_enabled, $is_loaded);
    }

    /**
     * @param  string  $package
     * @return bool
     */
    public function isPackageExisting(string $package): bool
    {
        return $this->getSummarizedPackages()->has($package);
    }

    /***** DOMAIN RELATED *****/

    /**
     * Get all local domains of Laravel or a specific package.
     *
     * @param  string|null  $package
     * @param  string|null  $domain
     * @param  Collection|null  $domains
     * @return Collection
     */
    public function getLocalDomains(string $package = null, string $domain = null, Collection &$domains = null): Collection
    {
        // If a package is not inside root packages folder then return null.
        if ($package && ! $this->isPackageLocal($package)) {
            return collect();
        }

        if (is_null($domains)) {
            $domains = collect();
        }

        if (($path = package_domain_domains_path($package, $domain, true)) && file_exists($path)) {
            $found = collect(File::directories($path))
                ->mapWithKeys(function ($path) {
                    $path = str_replace('\\', '/', $path);

                    return [domain_encode($path) => $path];
                });

            if ($found->isNotEmpty()) {
                $domains = $domains->merge($found);
                $found->each(function ($value, $key) use ($package, &$domains) {
                    $domains = $domains->merge($this->getLocalDomains($package, $key));
                });
            }
        }

        return $domains;
    }

    /**
     * Check whether a specific domain is local or not.
     *
     * @param  string  $domain
     * @param  string|null  $package
     * @return bool
     */
    public function isDomainLocal(string $domain, string $package = null): bool
    {
        return $this->getLocalDomains($package)->has($domain);
    }

    /**
     * Get all enabled domains of Laravel or a specific package.
     *
     * @param  string|null  $package
     * @return Collection
     */
    public function getEnabledDomains(string $package = null): Collection
    {
        if ($package) {
            $res = $this->getSummarizedPackages()->get($package);
            $path = $res ? $res['path'] : null;
        } else {
            $path = starterKit()->getRoot()->get('path');
        }

        if ($path && $namespaces = get_contents_from_composer_json($path, 'autoload.psr-4')) {
            return $namespaces->mapWithKeys(function ($directory, $namespace) {
                return [domain_encode($namespace) => $directory];
            })
                ->filter(fn ($value, $key) => $key);
        }

        return collect();
    }

    /**
     * Check whether a specific domain is enabled or not.
     *
     * @param  string  $domain
     * @param  string|null  $package
     * @return bool
     */
    public function isDomainEnabled(string $domain, string $package = null): bool
    {
        return $this->getEnabledDomains($package)->has($domain);
    }

    /**
     * Get all loaded domains of Laravel or a specific package.
     *
     * @param  string|null  $package
     * @return Collection
     */
    public function getLoadedDomains(string $package = null): Collection
    {
        return starterKit()->getDomains($package) ?? collect();
    }

    /**
     * Check whether a specific domain is loaded or not.
     *
     * @param  string  $domain
     * @param  string|null  $package
     * @return bool
     */
    public function isDomainLoaded(string $domain, string $package = null): bool
    {
        return $this->getLoadedDomains($package)->has($domain);
    }

    /**
     * @param  string|null  $package
     * @param  string|array|null  $filter
     * @param  bool|null  $is_local
     * @param  bool|null  $is_enabled
     * @param  bool|null  $is_loaded
     * @param  bool  $with_providers
     * @return Collection
     */
    public function getSummarizedDomains(
        string $package = null,
        string|array $filter = null,
        bool $is_local = null,
        bool $is_enabled = null,
        bool $is_loaded = null,
        bool $with_providers = false,
    ): Collection {
        $local = $this->getLocalDomains($package);
        $enabled = $this->getEnabledDomains($package);
        $loaded = $this->getLoadedDomains($package);

        $result = $this->getMergedCollections($local, $loaded, $enabled, $filter, $is_local, $is_enabled, $is_loaded);

        if ($with_providers) {
            return $result->map(function ($value, $key) {
                $path = guess_file_or_directory_path($value['path'], StarterKit::PROVIDERS_DIR);
                $providers = collect_classes_from_path($path, 'ServiceProvider');
                $value['providers'] = $providers;

                return $value;
            });
        }

        return $result;
    }

    /**
     * Check whether a domain exists or not.
     *
     * @param  string  $domain
     * @param  string|null  $package
     * @return bool
     */
    public function isDomainExisting(string $domain, string $package = null): bool
    {
        return $this->getSummarizedDomains($package)->has($domain);
    }

    /**
     * @param  string  $domain
     * @param  string|null  $package
     * @param  bool|null  $is_local
     * @param  bool|null  $is_enabled
     * @param  bool|null  $is_loaded
     * @param  bool  $with_providers
     * @param  bool  $with_child
     * @return Collection
     */
    public function getParentDomains(
        string $domain,
        string $package = null,
        bool $is_local = null,
        bool $is_enabled = null,
        bool $is_loaded = null,
        bool $with_providers = false,
        bool $with_child = false,
    ): Collection {
        $domains = explode('.', $domain);
        $parents = [];
        for ($i = 0; $i < count($domains) - ($with_child ? 0 : 1); $i++) {
            // Create parent domain first before subdomains
            $slice = array_slice($domains, 0, $i + 1);
            $parents[] = implode('.', $slice);
        }

        return $this->getSummarizedDomains(package: $package, is_local: $is_local, is_enabled: $is_enabled, is_loaded: $is_loaded, with_providers: $with_providers)
            ->only($parents);
    }

    /**
     * @param  string  $domain
     * @param  string|null  $package
     * @param  bool|null  $is_local
     * @param  bool|null  $is_enabled
     * @param  bool|null  $is_loaded
     * @param  bool  $with_providers
     * @param  bool  $with_parent
     * @return Collection
     */
    public function getSubDomains(
        string $domain,
        string $package = null,
        bool $is_local = null,
        bool $is_enabled = null,
        bool $is_loaded = null,
        bool $with_providers = false,
        bool $with_parent = false,
    ): Collection {
        return $this->getSummarizedDomains(package: $package, is_local: $is_local, is_enabled: $is_enabled, is_loaded: $is_loaded, with_providers: $with_providers)
            ->filter(fn ($value, $key) => Str::startsWith($key, $domain.'.') || ($with_parent && $key === $domain));
    }

    /**
     * @param  Collection  $local
     * @param  Collection  $loaded
     * @param  Collection  $enabled
     * @param  array|string|null  $filter
     * @param  bool|null  $is_local
     * @param  bool|null  $is_enabled
     * @param  bool|null  $is_loaded
     * @return Collection|mixed
     */
    private function getMergedCollections(
        Collection $local,
        Collection $loaded,
        Collection $enabled,
        array|string|null $filter,
        ?bool $is_local,
        ?bool $is_enabled,
        ?bool $is_loaded
    ): mixed {
        return $local->merge($loaded)->map(function ($path, $package) use ($local, $loaded, $enabled) {
            $details = [
                'is_local' => $local->has($package),
                'is_enabled' => $enabled->has($package),
                'is_loaded' => $loaded->has($package),
            ];

            // Path in this case might be all details about the package
            if (is_string($path)) {
                $details['path'] = $path;
            } else {
                $details = array_merge($details, $path);
            }

            return $details;
        })
            ->when(
                $filter,
                fn (Collection $collection) => $collection->filter(fn ($value, $key) => Str::contains($key, $filter))
            )
            ->when(
                ! is_null($is_local),
                fn (Collection $collection) => $collection->filter(fn (array $arr) => $arr['is_local'] == $is_local)
            )
            ->when(
                ! is_null($is_enabled),
                fn (Collection $collection) => $collection->filter(fn (array $arr) => $arr['is_enabled'] == $is_enabled)
            )
            ->when(
                ! is_null($is_loaded),
                fn (Collection $collection) => $collection->filter(fn (array $arr) => $arr['is_loaded'] == $is_loaded)
            );
    }
}
