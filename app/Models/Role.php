<?php

namespace App\Models;

use App\Original\Util\SessionUtil;

class Role extends AbstractModel {
    // テーブル名
    protected $table = 'roles';
    
    // 変更可能なカラム
    protected $fillable = [
        'role_name', 
        'priority', 
        'created_at', 
        'updated_at', 
        'created_by', 
        'updated_by'
    ];
    
    public static function options() {
        // ユーザー情報を取得(セッション)
        $priority = SessionUtil::getUser()->getRolePriority();
        
        return Role::where( 'priority', '>=' , $priority )
                   ->orderBys( ['id' => 'asc'] )
                   ->lists( 'role_name', 'id' );
    }

}
