<?php

namespace App\Models;

use App\Lib\Util\DateUtil;
use App\Lib\Codes\DispCodes;
use Illuminate\Database\Eloquent\SoftDeletes;
use Log;
use DB;

/**
 * 活動実績の計画値登録のモデル
 */
class Plan extends AbstractModel {
    
    use SoftDeletes;
    
    // テーブル名
    protected $table = 'tb_plan';

    // 変更可能なカラム
    protected $fillable = [
        'plan_base_id',
        'plan_user_id',
        'plan_ym',
        'plan_data'
    ];

    ######################
    ## other
    ######################
    
    /**
     * スタッフを動的にバインド
     */
    public function staff() {
        return $this->hasOne( 'App\Models\UserAccount', 'id', 'plan_user_id' );
    }
    
    /**
     * データの登録と更新
     * @param  [type] $values [description]
     * @return [type]         [description]
     */
    public static function merge( $values ) {
        //\Log::debug( $values );

        if ( !is_null( $values['plan_base_id'] ) == True || !is_null( $values['plan_ym'] ) ){
            // 統合車両管理Noと作業内容と次回車検日が合致するデータは更新
            Plan::updateOrCreate(
                [
                    'plan_base_id' => $values['plan_base_id'],
                    'plan_user_id' => $values['plan_user_id'],
                    'plan_ym' => $values['plan_ym']
                ],
                $values
            );
            
        }
    }

}
