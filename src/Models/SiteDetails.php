<?php

namespace CMSCore\Models;

use App\Traits\HasMediaUpload;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property int $site_id
 * @property string|null $favicon
 * @property string|null $logo
 * @property string|null $dark_logo
 * @property string|null $contact_email
 * @property string|null $facebook_link
 * @property string|null $pinterest_link
 * @property string|null $instagram_link
 * @property string|null $twitter_link
 * @property string|null $tiktok_link
 * @property string|null $youtube_link
 * @property string|null $header_script
 * @property string|null $footer_script
 * @property array<array-key, mixed>|null $affiliate_networks
 * @property bool $hide_from_search
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \CMSCore\Models\Site $site
 * @property-read mixed $favicon_url
 * @property-read mixed $logo_url
 * @property-read mixed $dark_logo_url
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteDetails newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteDetails newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteDetails query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteDetails whereAffiliateNetworks($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteDetails whereContactEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteDetails whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteDetails whereDarkLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteDetails whereFacebookLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteDetails whereFavicon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteDetails whereFooterScript($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteDetails whereHeaderScript($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteDetails whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteDetails whereInstagramLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteDetails whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteDetails wherePinterestLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteDetails whereSiteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteDetails whereTiktokLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteDetails whereTwitterLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteDetails whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteDetails whereYoutubeLink($value)
 *
 * @mixin \Eloquent
 */
class SiteDetails extends Model implements HasMedia
{
    use HasMediaUpload, InteractsWithMedia;

    protected $guarded = ['id'];

    protected $casts= [
        'affiliate_networks' => 'array',
        'hide_from_search'   => 'boolean',
    ];

    public $appends = ['favicon_url', 'logo_url', 'dark_logo_url'];

    public function site(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Falls back to the legacy `favicon` string column until existing rows
     * are backfilled into the media library.
     */
    protected function faviconUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstMediaUrl('favicon') ?: $this->favicon,
        );
    }

    /**
     * Falls back to the legacy `logo` string column until existing rows are
     * backfilled into the media library.
     */
    protected function logoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstMediaUrl('logo') ?: $this->logo,
        );
    }

    /**
     * Falls back to the legacy `dark_logo` string column until existing rows
     * are backfilled into the media library.
     */
    protected function darkLogoUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->getFirstMediaUrl('dark_logo') ?: $this->dark_logo,
        );
    }
}
