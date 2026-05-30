<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;


class TaskStatus extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'name',
        'label',
        'color',
        'sort_order',
    ];

    /**
     * リレーション
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * デフォルトステータス取得（最小sort_order）
     */
    public static function getDefault(int $organizationId): ?self
    {
        return self::where('organization_id', $organizationId)
            ->orderBy('sort_order')
            ->first();
    }

    /**
     * 名前で取得（Enum的に使う）
     */
    public static function findByName(string $name, int $organizationId): ?self
    {
        return self::where('name', $name)
            ->where('organization_id', $organizationId)
            ->first();
    }

    /**
     * 定数（Enum代替）
     */
    public const TODO = 'Todo';
    public const IN_PROGRESS = 'In Progress';
    public const REVIEW = 'Review';
    public const DONE = 'Done';
}
