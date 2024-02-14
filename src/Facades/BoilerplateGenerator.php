<?php

namespace Luchavez\BoilerplateGenerator\Facades;

use Illuminate\Cache\CacheManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;

/**
 * @method static string getMainTag()
 * @method static bool isPestEnabled()
 * @method static string getAuthorName()
 * @method static void setAuthorName(string|null $author_name)
 * @method static string getAuthorEmail()
 * @method static void setAuthorEmail(string|null $author_email)
 * @method static string getAuthorHomepage()
 * @method static void setAuthorHomepage(string|null $author_homepage)
 * @method static string getPackageSkeleton()
 * @method static Collection getLocalPackages()
 * @method static bool isPackageLocal(string $package)
 * @method static Collection getEnabledPackages()
 * @method static bool isPackageEnabled(string $package)
 * @method static Collection getLoadedPackages(bool $with_details = false)
 * @method static bool isPackageLoaded(string $package)
 * @method static Collection getSummarizedPackages(string|array|null $filter = null, bool|null $is_local = null, bool|null $is_enabled = null, bool|null $is_loaded = null, bool $with_details = false)
 * @method static bool isPackageExisting(string $package)
 * @method static Collection getLocalDomains(string|null $package = null, string|null $domain = null, Collection|null $domains = null)
 * @method static bool isDomainLocal(string $domain, string|null $package = null)
 * @method static Collection getEnabledDomains(string|null $package = null)
 * @method static bool isDomainEnabled(string $domain, string|null $package = null)
 * @method static Collection getLoadedDomains(string|null $package = null)
 * @method static bool isDomainLoaded(string $domain, string|null $package = null)
 * @method static Collection getSummarizedDomains(string|null $package = null, string|array|null $filter = null, bool|null $is_local = null, bool|null $is_enabled = null, bool|null $is_loaded = null, bool $with_providers = false)
 * @method static bool isDomainExisting(string $domain, string|null $package = null)
 * @method static Collection getParentDomains(string $domain, string|null $package = null, bool|null $is_local = null, bool|null $is_enabled = null, bool|null $is_loaded = null, bool $with_providers = false, bool $with_child = false)
 * @method static Collection getSubDomains(string $domain, string|null $package = null, bool|null $is_local = null, bool|null $is_enabled = null, bool|null $is_loaded = null, bool $with_providers = false, bool $with_parent = false)
 * @method static CacheManager getCacheManager()
 * @method static void setCacheManager(CacheManager|null $cache_manager)
 * @method static bool isCacheTaggable()
 * @method static bool clearCache()
 * @method static bool forgetCache(string[] $tags, string $key)
 * @method static void validateClass(string $class, string $base_class)
 *
 * @see \Luchavez\BoilerplateGenerator\Services\BoilerplateGenerator
 */
class BoilerplateGenerator extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'boilerplate-generator';
    }
}
