<?php

namespace App\Commands\Hoken\ResultStaff;

use App\Models\Hoken\InsuranceStaffDB;
use App\Commands\Command;

/**
 * 個人販売計画登録一覧を取得するコマンド
 *
 * @author yhatsutori
 */
class ListCommand extends Command{
    
    /**
     * コンストラクタ
     * @param array $requestObj [description]
     */
    public function __construct( $requestObj = [] ){
        $this->requestObj = $requestObj;
    }
    
    /**
     * メインの処理
     * @return [type] [description]
     */
    public function handle(){
        // 指定された拠点コードの対象年に該当する成約の値を取得
        $data = InsuranceStaffDB::getPlanList(
            $this->requestObj->year
        );
        
        return $data;
    }
    
}
