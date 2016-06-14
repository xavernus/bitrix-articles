<?

Class ax_articles extends CModule
{
	var $MODULE_ID = "ax_articles";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;

	function ax_articles()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}

		$this->MODULE_NAME = "'Статьи' – модуль с компонентом";
		$this->MODULE_DESCRIPTION = "";
	}

	function InstallFiles($arParams = array())
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/ax_articles/install/components",
		             $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFilesEx("/bitrix/components/ax_articles");
		return true;
	}

	function DoInstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION, $DB;

		CModule::IncludeModule('iblock');

		$errorFlag = true;

		$arFields = Array(
		    'ID'=>'ax_articles_module',
		    'SECTIONS'=>'Y',
		    'IN_RSS'=>'N',
		    'SORT'=>100,
		    'LANG'=>Array(
		        'en'=>Array(
		            'NAME'=>'Articles',
		            'SECTION_NAME'=>'Folders',
		            'ELEMENT_NAME'=>'Elements'
		        ),
		    	'ru'=>Array(
		            'NAME'=>'Статьи',
		            'SECTION_NAME'=>'Разделы',
		            'ELEMENT_NAME'=>'Элементы'
		        ),
		    )
		);

		$iblockType = new CIBlockType;
		$DB->StartTransaction();
		$flag = $iblockType->Add($arFields);

		if($flag) {
			$DB->Commit();
		} else {
			$DB->Rollback();
		   	CAdminMessage::ShowMessage('Error: '.$iblockType->LAST_ERROR);
		   	$errorFlag = false;
		}

		$iblock = new CIBlock;
		$arFields = Array(
		  	"ACTIVE" => "Y",
		  	"NAME" => "Статьи",
		  	"CODE" => "ax_articles",
		  	"LIST_PAGE_URL" => "",
		  	"DETAIL_PAGE_URL" => "#ELEMENT_CODE#/",
		  	"IBLOCK_TYPE_ID" => 'ax_articles_module',
		  	"SITE_ID" => Array("s1"),
		  	"SORT" => 100,
		  	"GROUP_ID" => Array("2"=>"D", "3"=>"R")
		);

		$DB->StartTransaction();
		$flag = $iblock->Add($arFields);

		if($flag) {
			$DB->Commit();
		} else {
			$DB->Rollback();
		   	CAdminMessage::ShowMessage('Error: '.$iblock->LAST_ERROR);
		   	$errorFlag = false;
		}

		if($errorFlag) {
			RegisterModule("ax_articles");
			CAdminMessage::ShowMessage("Модуль 'Статьи' установлен");
		} else {
			CAdminMessage::ShowMessage("Не удалось установить модуль");
		}
		
		$APPLICATION->IncludeAdminFile("Установка модуля 'Статьи'", $DOCUMENT_ROOT."/bitrix/modules/ax_articles/install/step.php");
	}

	function DoUninstall() 
	{
		global $DOCUMENT_ROOT, $APPLICATION, $DB;

		CModule::IncludeModule('iblock');

		$errorFlag = true;

		$iblock = CIBlock::GetList(Array(), Array('CODE'=>'ax_articles'), true);
		$iblock = $iblock->Fetch();
		
		$DB->StartTransaction();
	    if(!CIBlock::Delete($iblock['ID'])) {
	        $DB->Rollback();
	        $errorFlag = false;
	    } else {
	        $DB->Commit();
	    }

	    $DB->StartTransaction();
		if(!CIBlockType::Delete('ax_articles_module'))
		{
		    $DB->Rollback();
		    $errorFlag = false;
		} else {
			$DB->Commit();
		}
		
	    if($errorFlag) {
			UnRegisterModule("ax_articles");
			CAdminMessage::ShowMessage("Модуль 'Статьи' удален");
		} else {
			CAdminMessage::ShowMessage("Не удалось удалить модуль");
		}
		
		$APPLICATION->IncludeAdminFile("Деинсталляция модуля 'Статьи'", $DOCUMENT_ROOT."/bitrix/modules/ax_articles/install/unstep.php");
	}
}
?>