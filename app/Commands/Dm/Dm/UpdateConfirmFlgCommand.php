<?php

namespace App\Commands\Dm\Dm;

use App\Lib\Util\DateUtil;
use App\Original\Util\SessionUtil;
use App\Models\DmConfirm;
use App\Commands\Command;

/**
 * DM送付リスト確認チェックのボタンを押したときの処理
 * @author  yhatsutori
 */
class UpdateConfirmFlgCommand extends Command{
//
//    /**
//     * コンストラクタ
//     */
//    public function __construct( $dm_type ){
//        // ユーザー情報を取得(セッション)
//        $this->loginAccountObj = SessionUtil::getUser();
//        $this->dm_type =  $dm_type ;
//    }
//
//    /**
//     * メインの処理
//     */
//    public function handle(){
//        // 2ヶ月後現在の年月
//        $inspection_ym = DateUtil::toYm( date( 'Y-m-01', strtotime( '+2 month' ) ) );
//
//        // 10日までは前月を指定
//        if( date("d") <= 10 ){
//            $inspection_ym = DateUtil::toYm( date( 'Y-m-01', strtotime( '+1 month' ) ) );
//        }
//
//        // 更新するカラム
////        $values = [
////            'user_id' => $this->loginAccountObj->getUserId(), // ログインしているユーザーID
////            'base_code' => $this->loginAccountObj->getBaseCode(), // ログインいているユーザーの拠点コード
////            //'inspection_ym' => DateUtil::toYm( DateUtil::now() ), // 現在の年月
////            'inspection_ym' => $inspection_ym, // 2ヶ月後現在の年月
////            'dm_confirm_flg' => 1, // チェックのフラグ
////        ];
//        $values['user_id'] = $this->loginAccountObj->getUserId(); // ログインしているユーザーID
//        $values['base_code'] = $this->loginAccountObj->getBaseCode(); // ログインいているユーザーの拠点コード
//        $values['inspection_ym'] = $inspection_ym; // 2ヶ月後現在の年月
//        $values['dm_confirm_flg'] = 1; // チェック報告のフラグ
//        // チェック報告の種類
//        if( $this->dm_type == '車検6ヶ月前点検' ){
//            $values['flg_6month_before'] = 1;
//        }elseif( $this->dm_type == '車検' ){
//            $values['flg_souki_nyuko'] = 1;
//        }elseif( $this->dm_type == '点検' ){
//            $values['flg_tenken'] = 1;
//        }
//
//        /**
//         * チェックの登録更新処理
//         * 次のカラムが一致した場合に更新する
//         * ・ユーザーID
//         * ・チェックの対象年月
//         */
//        DmConfirm::updateOrCreate(
//            [
//                'user_id' => $values['user_id'],
//                'inspection_ym' => $values['inspection_ym']
//            ],
//            $values
//        )
//        ->save();
//    }
    
}
