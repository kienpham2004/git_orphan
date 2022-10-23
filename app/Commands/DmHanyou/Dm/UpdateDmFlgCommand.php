<?php

namespace App\Commands\DmHanyou\Dm;

use App\Models\DmHanyou\CustomerDm;
use App\Commands\Command;

/**
 * DM送付不要チェックの更新処理
 *
 * @author yhatsutori
 */
class UpdateDmFlgCommand extends Command{
//
//    /**
//     * コンストラクタ
//     * @param instance $requestObj
//     */
//    public function __construct( $requestObj ){
//        $this->requestObj = $requestObj;
//    }
//
//    /**
//     * メインの処理
//     */
//    public function handle(){
//        foreach( $this->requestObj->targets as $dm_target ){
//            \Log::debug("UpdateDmFlgCommand");
//            \Log::debug($dm_target);
//
//            $customerDmMObj = CustomerDm::findOrFail( $dm_target['id'] );
//
//            $customerDmMObj->original_dm_flg = $dm_target['is_dm_flg']; // 不要チェックのフラグ
//            $customerDmMObj->original_dm_unnecessary_reason = $dm_target['dm_unnecessary_reason']; // 不要理由
//
//            // 不要チェックがなくても登録可能
//            //$customerDmMObj->original_car_new_old_flg = $dm_target['car_new_old_flg']; // 新車/中古車
//
//            // 不要チェックのある時だけ操作
//            if( $customerDmMObj->original_dm_flg == 0 ){
//                $customerDmMObj->original_dm_flg = NULL;
//                $customerDmMObj->original_dm_unnecessary_reason = NULL;
//            }
//
//            $customerDmMObj->save();
//        }
//    }
//
}
