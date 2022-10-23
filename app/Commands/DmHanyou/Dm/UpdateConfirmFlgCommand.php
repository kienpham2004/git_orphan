<?php

namespace App\Commands\DmHanyou\Dm;

use App\Lib\Util\DateUtil;
use App\Original\Util\SessionUtil;
use App\Models\DmHanyou\CustomerDmConfirm;
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
//    public function __construct(){
//        // ユーザー情報を取得(セッション)
//        $this->loginAccountObj = SessionUtil::getUser();
//    }
//
//    /**
//     * メインの処理
//     */
//    public function handle(){
//        // 決め打ち
//        $inspection_ym = "201711";
//
//        // 更新するカラム
//        $values = [
//            'user_id' => $this->loginAccountObj->getUserId(), // ログインしているユーザーID
//            'base_code' => $this->loginAccountObj->getBaseCode(), // ログインいているユーザーの拠点コード
//            //'inspection_ym' => DateUtil::toYm( DateUtil::now() ), // 現在の年月
//            'inspection_ym' => $inspection_ym, // 2ヶ月後現在の年月
//            'dm_confirm_flg' => 1 // チェックのフラグ
//        ];
//
//        /**
//         * チェックの登録更新処理
//         * 次のカラムが一致した場合に更新する
//         * ・ユーザーID
//         * ・チェックの対象年月
//         */
//        CustomerDmConfirm::updateOrCreate(
//            [
//                'user_id' => $values['user_id'],
//                'inspection_ym' => $values['inspection_ym']
//            ],
//            $values
//        )
//        ->save();
//    }
//
}
