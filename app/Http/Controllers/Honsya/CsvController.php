<?php

namespace App\Http\Controllers\Honsya;

use App\Original\Util\CodeUtil;
use App\Original\Util\SessionUtil;
use App\Models\UploadHistory;
use App\Commands\Csv\CsvUploadCommand;
use App\Http\Requests\CsvUploadRequest;
use App\Http\Controllers\Controller;
use Session;
use App\Lib\Util\DateUtil;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Redirect;

use App\Models\ManageInfo;
use App\Models\TargetCars;

/**
 * 本社コントローラー
 *
 * @author yhatsutori
 *
 */
class CsvController extends Controller {

	/**
	 * コンストラクタ
	 */
	public function __construct(){
		// 表示部分で使うオブジェクトを作成
		$this->initDisplayObj();
//        $this->mi_updated  = $this->getManageInfo();
//        $this->tgc_updated = $this->getTargetCars();
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
		$this->displayObj->page = "upload";
		// 基本のテンプレート
		$this->displayObj->tpl = $this->displayObj->category . "." . $this->displayObj->page;
		// コントローラー名
		$this->displayObj->ctl = "Honsya\CsvController";
	}

	#######################
	## Controller method
	#######################

	/**
	 * アップロード画面
	 * @return [type] [description]
	 */
	public function getUpload(){
		// アップロード権限がない場合は、スタッフ画面へリダイレクト
		if( in_array( SessionUtil::getUser()->getRolePriority(), [1,2,3] ) == False ){
			return redirect( action( "Honsya\UserController" . '@getSearch' ) );
		}else{
			//
			$uploadHistoryList = UploadHistory::findAll();
            // データの有無チェック
            if( $this->checkUploading() !== false ){
                list( $mi_updated, $tgc_updated ) = $this->checkUploading();
            }

            // 処理時間を記録
            $runTime = DateUtil::getRunTime();
			return view(
				$this->displayObj->tpl . '.upload',
				compact(
					'uploadHistoryList',
                                        'mi_updated',
                                        'tgc_updated',
                                        'runTime'
				)
			);

		}
	}

	/**
	 * CSVファイルアップロード
	 *
	 * @param CsvUploadRequest $request
	 */
    public function postUpload( CsvUploadRequest $request ){
        // 拡張チェックを行う
        $file = $request->file('csv_file');
        $validator = App::make('App\Http\Requests\ValidateUploadRequest');
        $validator = $validator->validate($file);
        if ( $validator->fails() )  {
            return Redirect::back()->withErrors($validator);
        }

		// CSVアップロードの時間を上げる
		set_time_limit(18432);
		// メモリの上限を変更
		ini_set('memory_limit', '4096M');
		
		#try{
			
			$csv = $this->dispatch(
				//new CsvUploadCommand($request, False)
				new CsvUploadCommand(
					$request,
					False // アップロードするだけの時はFalseを指定
					//True // webから実行するときはTrueを指定
				)
			);
    
			$csverrors = "";
			$info = "";
                        
                        // ファイル名判定結果
//                        if( $csv === "NG" ){
//                            $csverrors = array(
//                                [
//                                    'message' => 'NG'
//                                ]
//                            );
//                        }
//                        // エラー判定結果
//                        elseif( $csv->result->hasError() ) {

			if( $csv->result->hasError() ) {
				$csverrors = $csv->result->errors();
			} else {
				$info = trans('messages.success.done');
			}
			
		#} catch ( \Exception $e ) {
		#	\Log::debug( $e );
		#}
                // ファイル名判定は文字列なので除外
//                if( is_object($csv) ){
                    // アップロード時のCSVチェック
                    $check_target = $csv->filePath;
                    CsvUploadCommand::csvErrorCheck( $csverrors, $check_target );
//                }

                // 履歴の呼び出し
                $uploadHistoryList = UploadHistory::findAll();
                
                // データの有無チェック
                if( $this->checkUploading() !== false ){
                    list( $mi_updated, $tgc_updated ) = $this->checkUploading();
                }
                

                // エラーセッションを用意
                if( !empty($csverrors) ){
                    // エラー数が１の場合
                    if( count($csverrors) == 1 ){
                        // ファイル名判定がNGかどうか
//                        if( $csverrors[0]['message'] === "NG" ){
//                            Session::put('update_error_csv', 1);
//                        }
//                        // 必須項目不足エラーかどうか
//                        elseif( strpos($csverrors[0]['message'], "必須") === FALSE ){
                        
                        if( strpos($csverrors[0]['message'], "必須") === FALSE ){
                            Session::put('update_error', 1);
                        }else{
                            Session::put('update_error_light', 1);
                        }
                    }else{ // エラー数が複数の場合
                        Session::put('update_error_light', 1);
                    }
                }else{ // 更新のセッションを用意
                    Session::put('update', 1);
                }

        // 処理時間を記録
        $runTime = DateUtil::getRunTime();
		return view(
			$this->displayObj->tpl . '.upload',
			compact(
				'uploadHistoryList',
				'csverrors',
				'info',
                'mi_updated',
                'tgc_updated',
                'runTime'
			)
		)
		->with( 'target_ym', $request->target_ym );

	}
        
        
	/**
	 * 統合データの更新チェック
         * （値の有無チェック）
	 */
//    public function getManageInfo() {
//        $mi_date = ManageInfo::orderBy('updated_at', 'desc')->limit(1)->lists('updated_at');
//        return (!empty($mi_date)) ? $mi_date : "";
//    }
//    public function getTargetCars() {
//        $tgc_date = TargetCars::orderBy('updated_at', 'desc')->limit(1)->lists('updated_at');
//        return (!empty($tgc_date)) ? $tgc_date : "";
//    }
        
	/**
	 * 統合データの更新チェック
         * ・存在する場合
         * 　@return array $mi_updated, $tgc_updated
         * ・存在しない場合
         * 　@return false
	 */
    public function checkUploading() {
//
//        if( !isset($this->mi_updated[0]) || !isset($this->tgc_updated[0]) ){
//            return false;
//        }else{
//            $now = date('Y-m-d H:i:s');
//            // 更新中かどうかの判定用（更新日時＋10分 が 現在より未来なら「更新中」）
//            $mi_updated_10  = date('Y-m-d H:i:s', strtotime($this->mi_updated[0].'+ 10 minute'));
//            $tgc_updated_10 = date('Y-m-d H:i:s', strtotime($this->tgc_updated[0].'+ 10 minute'));
//            // viewに送る値を生成
//            $mi_updated  = ($now > $mi_updated_10) ? $this->mi_updated[0] : "更新中";
//            $tgc_updated = ($now > $tgc_updated_10) ? $this->tgc_updated[0] : "更新中";
//
//            return array($mi_updated, $tgc_updated);
//        }
        $flgLongBatch = CodeUtil::checkUpdateStatus(3); // LongのCSV更新中フラグ
        // Long : 顧客ＣＳＶ更新中 又は　バッチ処理完了
        if ($flgLongBatch == 1 || file_exists(storage_path() . '/upload/check_long_batch') != 1 ) {
            $value = UploadHistory::orderBy('type_code', 'asc')->whereIn('type_code', [99])->lists('updated_at');
            $longBatch = (!empty($value[0])) ? date('Y/m/d H:i:s', strtotime($value[0])) : "未設定";
        } else{
            $longBatch = "更新中";
        }

        // Short : バッチ処理完了 又は　各ＣＳＶ更新中
        if (file_exists(storage_path() . '/upload/check_short_batch_manager') != 1 ||
            file_exists(storage_path() . '/upload/check_short_batch_csv') == 1) {
            $value = UploadHistory::orderBy('type_code', 'asc')->whereIn('type_code', [88])->lists('updated_at');
            $shortBatch = (!empty($value[0])) ? date('Y/m/d H:i:s', strtotime($value[0])) : "未設定";
        } else{
            $shortBatch = "更新中";
        }
        return array($shortBatch, $longBatch);
    }

}
