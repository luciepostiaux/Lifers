<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LiferProfile extends Model
{
    public const RELATIONSHIP_SINGLE = 'single';

    public const RELATIONSHIP_COUPLE = 'couple';

    public const RELATIONSHIP_ENGAGED = 'engaged';

    public const RELATIONSHIP_MARRIED = 'married';

    public const RELATIONSHIP_MARRIED_WITH = 'married_with';

    public const RELATIONSHIP_OPEN = 'open_relationship';

    public const RELATIONSHIP_COMPLICATED = 'complicated';

    public const RELATIONSHIP_LONG_DISTANCE = 'long_distance';

    public const RELATIONSHIP_SEPARATED = 'separated';

    public const RELATIONSHIP_DIVORCED = 'divorced';

    public const RELATIONSHIP_WIDOWED = 'widowed';

    public const RELATIONSHIP_UNDECIDED = 'undecided';

    public const RELATIONSHIP_LABELS = [
        self::RELATIONSHIP_SINGLE => 'Célibataire',
        self::RELATIONSHIP_COUPLE => 'En couple',
        self::RELATIONSHIP_ENGAGED => 'Fiancé·e',
        self::RELATIONSHIP_MARRIED => 'Marié·e',
        self::RELATIONSHIP_MARRIED_WITH => 'Marié·e avec mon conjoint',
        self::RELATIONSHIP_OPEN => 'En relation libre',
        self::RELATIONSHIP_COMPLICATED => 'C’est compliqué',
        self::RELATIONSHIP_LONG_DISTANCE => 'En relation à distance',
        self::RELATIONSHIP_SEPARATED => 'Séparé·e',
        self::RELATIONSHIP_DIVORCED => 'Divorcé·e',
        self::RELATIONSHIP_WIDOWED => 'Veuf·veuve',
        self::RELATIONSHIP_UNDECIDED => 'Indécis·e',
    ];

    protected $primaryKey = 'lifer_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = ['lifer_id', 'content', 'show_money', 'relationship_status'];

    protected $casts = [
        'content' => 'array',
        'show_money' => 'boolean',
    ];

    public function lifer()
    {
        return $this->belongsTo(Lifer::class);
    }
}
