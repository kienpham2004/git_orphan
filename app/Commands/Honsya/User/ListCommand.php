<?php

namespace App\Commands\Honsya\User;

use App\Models\UserAccount;
use App\Commands\Command;

/**
 * 担当者一覧を取得するコマンド
 *
 * @author yhatsutori
 */
class ListCommand extends Command{
    
    /**
     * コンストラクタ
     * @param [type] $sort       [description]
     * @param [type] $requestObj [description]
     * @param [type] $user       [description]
     */
    public function __construct( $sort, $requestObj, $user ){
        $this->sort = $sort;
        $this->requestObj = $requestObj;
        $this->user = $user;

        // カラムとヘッダーの値を取得
        $csvParams = $this->getCsvParams();
        // カラムを取得
        $this->columns = array_keys( $csvParams );
        // ヘッダーを取得
        $this->headers = array_values( $csvParams );
    }

    /**
     * カラムとヘッダーの値を取得
     * @return array
     */
    private function getCsvParams(){
        return [
            'tb_user_account.*' => 'ALL',
            'tb_base.base_short_name' => '拠点'
        ];
    }


    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
    
        // 他のテーブルとJOIN
        $builderObj = UserAccount::joinBase();
        
        // 検索条件を指定
        $builderObj = $builderObj->whereRequest( $this->requestObj, $this->user );
        
        // 並び替えの処理
        $builderObj = $builderObj->orderBys( $this->sort['sort'] );

        // デバッグ
        // dd( $builderObj->toSql() );
        
        // ペジネートの処理
        $data = $builderObj
            ->paginate( $this->requestObj->row_num, $this->columns )
            // 表示URLをpagerに指定
            ->setPath('pager');

        return $data;
    }
    
}
