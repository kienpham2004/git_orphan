<?php

namespace App\Http\Controllers\Honsya;

use App\Commands\Honsya\Log\ListCommand;
use App\Http\Controllers\Controller;
use App\Http\Controllers\tInitSearch;
use App\Lib\Util\DateUtil;
use App\Models\LogMonth;
use App\Original\Util\SessionUtil;
use Illuminate\Http\Request;

class LogMonthController extends Controller {

	use tInitSearch;

	/**
	 * コンストラクタ
	 */
	public function __construct(){
		// 表示部分で使うオブジェクトを作成
		$this->initDisplayObj();
		$this->loginAccountObj = SessionUtil::getUser();
		
		// // 本社担当者権限を有しないとアクセス不可
		// $this->middleware(
		// 	'RoleTentyou',
		// 	['only' => ['getIndex', 'getSearch', 'getSort', 'getEdit', 'putEdit', 'getCreate', 'putCreate']]
		// );
	}

	#######################
	## initalize
	#######################
	
	/**
	 * 表示部分で使うオブジェクトを作成
	 * @return [type] [description]
	 */
	public function initDisplayObj(){
		// 表示部分で使うオブジェクトを作成
		$this->displayObj = app('stdClass');
		// カテゴリー名
		$this->displayObj->category = "honsya";
		// 画面名
		$this->displayObj->page = "log";
		// 基本のテンプレート
		$this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
		// コントローラー名
		$this->displayObj->ctl = "Honsya\LogMonthController";
	}
	
	#######################
	## 一覧画面
	#######################
	
	/**
	 * 一覧画面のデータを表示
	 * @param  [type] $search     [description]
	 * @param  [type] $sort   [description]
	 * @param  [type] $requestObj [description]
	 * @return [type]             [description]
	 */
	public function showListData( $search, $sort, $requestObj ){
		// 表示するデータを取得
		$showData = $this->dispatch(new ListCommand($sort, $requestObj));
        $totalNotDownload = self::checkIsNotDownloadFileOfThreeMonthBefore();
		return view(
			$this->displayObj->tpl . '.index',
			compact(
				'showData',
				'totalNotDownload'
			)
		)
		->with( "title", "ログ／一覧" );
	}

	/**
	 * ファイルのダウンロード
	 *  @param  [type] $requestObj [description]
	 */
	public function postDownload(Request $requestObj) {
		$file_name = $requestObj->input('file_name');
		$lm_ym = $requestObj->input('lm_ym');
		$path = storage_path().'/logzip_months/' . $file_name;
		if(file_exists($path)) {
			$file_month = [
				'lm_last_downloaded' => DateUtil::now(),
				'lm_last_downloaded_by' => $this->loginAccountObj->getUserId()
			];

			$this->updateLogMonth($lm_ym, $file_month);
			return response()->download($path, $file_name, ['Content-Type' => 'application/zip']);
		}

		return redirect()->back()->withInput();
	}

	/**
	 * 最終ダウンロードと誰か最終ダウンロードの情報を更新
	 * @param  [type] $log_ym [description]
	 * @param  [type] $data [description]
	 */
	private function updateLogMonth ($log_ym, $data) {
		return LogMonth::updateOrCreate(['lm_ym'=> $log_ym], $data);
	}

	/**
	 * 3ヶ月前のすべてログ情報を確認
	 */
	public static function checkIsNotDownloadFileOfThreeMonthBefore() {
		$data = LogMonth::getInfoNotDownloadOfThreeMonthBefore();
		return count($data);
	}
}
