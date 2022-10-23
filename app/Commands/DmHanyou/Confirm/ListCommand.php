<?php

namespace App\Commands\DmHanyou\Confirm;

use App\Models\UserAccount;
use App\Commands\Command;

/**
 * DM送付リスト確認画面で使うコマンド
 * @author yhatsutori
 */
class ListCommand extends Command{
//
//    /**
//     * コンストラクタ
//     * @param [type] $sort       [description]
//     * @param [type] $requestObj [description]
//     */
//    public function __construct( $sort, $requestObj ){
//        $this->sort = $sort;
//        $this->requestObj = $requestObj;
//
//        // カラムとヘッダーの値を取得
//        $csvParams = $this->getCsvParams();
//        // カラムを取得
//        $this->columns = array_keys( $csvParams );
//        // ヘッダーを取得
//        $this->headers = array_values( $csvParams );
//    }
//
//    /**
//     * カラムとヘッダーの値を取得
//     * @return array
//     */
//    private function getCsvParams(){
//        return [
//            'tb_user_account.user_id' => '',
//            'tb_user_account.user_name' => '',
//            'tb_user_account.account_level' => '',
//            'tb_base.base_code' => '',
//            'tb_base.base_short_name' => '',
//            'tb_customer_dm_confirm.dm_confirm_flg' => ''
//        ];
//    }
//
//    /**
//     * DM送付リスト確認画面の一覧データを取得
//     * @return collection
//     */
//    public function handle(){
//        // 他のテーブルとJOIN
//        $builderObj = UserAccount::joinBase()
//                                 ->JoinDmHanyouConfirm()
//                                 ->where('account_level', '<>', '1')
//                                 ->where('account_level', '<>', '2')
//                                 ->where('account_level', '<>', '3')
//                                 ->where('account_level', '<>', '5')
//                                 ->where('account_level', '<>', '7');
//
//        // 検索条件を指定
//        $builderObj = $builderObj->whereDmConfirmRequest( $this->requestObj );
//
//        // 並び替えの処理
//        $builderObj = $builderObj->orderBys( $this->sort['sort'] );
//
//        // ペジネートの処理
//        $data = $builderObj
//            ->paginate( $this->requestObj->row_num, $this->columns )
//            // 表示URLをpagerに指定
//            ->setPath('pager');
//
//        return $data;
//    }
}
