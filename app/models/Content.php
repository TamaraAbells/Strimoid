<?php namespace Strimoid\Models;

use Str, PDP;
use Summon\Summon;
use Illuminate\Database\Eloquent\SoftDeletes;
use Strimoid\Helpers\MarkdownParser;

/**
 * Content model
 *
 * @property string $_id
 * @property string $title Content title
 * @property string $description Content description
 * @property bool $eng Is content using foreign language?
 * @property bool $nsfw is Content "not safe for work"?
 * @property string $thumbnail Thumbnail filename
 * @property string $domain Domain
 * @property string $url URL address
 * @property DateTime $created_at
 */
class Content extends BaseModel
{

    use SoftDeletes;

    protected static $rules = [
        'title' => 'required|min:1|max:128|not_in:edit,thumbnail',
        'description' => 'max:255',
        'groupname' => 'required|exists_ci:groups,urlname'
    ];

    protected $attributes = [
        'uv' => 0,
        'dv' => 0,
        'score' => 0,
        'comments_count' => 0,
    ];

    protected $table = 'contents';
    protected $dates = ['deleted_at'];
    protected $appends = ['vote_state'];
    protected $fillable = ['title', 'description', 'nsfw', 'eng', 'text', 'url'];
    protected $hidden = ['text', 'text_source', 'updated_at'];

    function __construct($attributes = array())
    {
        $this->id = Str::random(6);

        static::deleted(function($content)
        {
            Notification::where('content_id', $this->getKey())->delete();

            if (!$content->trashed())
            {
                foreach ($this->getComments() as $comment)
                {
                    $comment->delete();
                }
            }
        });

        parent::__construct($attributes);
    }

    public function group()
    {
        return $this->belongsTo('Strimoid\Models\Group');
    }

    public function user()
    {
        return $this->belongsTo('Strimoid\Models\User')
            ->select(['avatar', 'name']);
    }

    public function deleted_by()
    {
        return $this->belongsTo('Strimoid\Models\User', 'deleted_by');
    }

    public function related()
    {
        return $this->hasMany('Strimoid\Models\ContentRelated');
    }

    public function comments()
    {
        return $this->hasMany('Strimoid\Models\Comment');
    }

    public function getDomain()
    {
        return $this->domain ?: 'strimoid.pl';
    }

    public function getEmbed()
    {
        if (!$this->embed)
        {
            $this->embed = Embed::make($this->getURL())->parseUrl();
        }

        return $this->embed;
    }

    public function getURL()
    {
        $this->url ?: $this->getSlug();
    }

    public function getSlug()
    {
        $params = [$this->_id, Str::slug($this->title)];
        return route('content_comments_slug', $params);
    }

    public function setNsfwAttribute($value)
    {
        $this->attributes['nsfw'] = toBool($value);
    }

    public function setEngAttribute($value)
    {
        $this->attributes['eng'] = toBool($value);
    }

    public function setUrlAttribute($url)
    {
        $this->attributes['url'] = $url;
        $this->attributes['domain'] = PDP::parseUrl($url)->host->registerableDomain;
    }

    public function setTextAttribute($text)
    {
        $parser = MarkdownParser::instance();
        $parser->config('inline_images', true);
        $parser->config('headers', true);

        $this->attributes['text'] = $parser->text(parse_usernames($text));
        $this->attributes['text_source'] = $text;
    }

    public function getThumbnailPath($width = null, $height = null)
    {
        $host = Config::get('app.cdn_host');

        if ($this->thumbnail && $width && $height)
        {
            return $host .'/'. $width .'x'. $height .'/thumbnails/'. $this->thumbnail;
        }
        elseif ($this->thumbnail)
        {
            return $host .'/thumbnails/'. $this->thumbnail;
        }

        return '';
    }

    public function autoThumbnail()
    {
        try {
            $summon = new Summon($this->getURL());
            $thumbnails = $summon->fetch();

            $this->setThumbnail($thumbnails['thumbnails'][0]);
        } catch(Exception $e){
        }
    }

    public function setThumbnail($path)
    {
        if ($this->thumbnail)
        {
            File::delete(Config::get('app.uploads_path').'/thumbnails/'. $this->thumbnail);
        }

        if (starts_with($path, '//'))
        {
            $path = 'http:'. $path;
        }

        $data = file_get_contents($path);
        $filename = Str::random(9) .'.png';

        $img = Image::make($data);
        $img->fit(400, 300);
        $img->save(Config::get('app.uploads_path').'/thumbnails/'. $filename);

        $this->thumbnail = $filename;
        $this->save();
    }

    public function removeThumbnail()
    {
        if ($this->thumbnail)
        {
            File::delete(Config::get('app.uploads_path').'/thumbnails/'. $this->thumbnail);
            $this->unset('thumbnail');
        }
    }

    public function isSaved()
    {
        return in_array($this->_id, (array) Auth::user()->data->_saved_contents);
    }

    public static function validate($input)
    {
        $validator = Validator::make($input, static::$rules);

        $validator->sometimes('text', 'required|min:1|max:50000', function($input)
        {
            return $input->text;
        });

        $validator->sometimes('url', 'required|url|safe_url|max:2048', function($input)
        {
            return !$input->text;
        });

        return $validator;
    }

    /* Permissions */

    public function canEdit(User $user = null)
    {
        $isAuthor = $user->_id == $this->user_id;
        $hasTime = Carbon::instance($this->created_at)->diffInMinutes() < 30;

        $isAdmin = $user->type == 'admin';

        return ($isAuthor && $hasTime) || $isAdmin;
    }

    public function canRemove(User $user = null)
    {
        return $user->isModerator($this->group);
    }

    /* Scopes */

    public function scopeFrontpage($query, $exists = true)
    {
        return $query->where('frontpage_at', 'exists', $exists);
    }

}