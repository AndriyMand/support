<?php
/**
 * ALTASIB
 * @site http://www.altasib.ru
 * @email dev@altasib.ru
 * @copyright 2006-2018 ALTASIB
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use ALTASIB\Support;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

if (!Main\Loader::includeModule("altasib.support")) {
    ShowError("ALTASIB_SUPPORT_MODULE_NOT_INSTALL");
    return;
}
$arParams["ID"] = (int)$arParams["ID"];

if (!($arParams['Right'] instanceof ALTASIB\Support\Rights)) {
    $arParams['Right'] = new ALTASIB\Support\Rights($USER->getId(), $arParams['ID']);
}

$arParams["ROLE"] = $Role = $arParams['Right']->getRole();
if ($arParams['Right']->getRole() == 'D') {
    $APPLICATION->AuthForm('');
}

$arParams['IS_SUPPORT_TEAM'] = $arParams['Right']->isSupportTeam();
$arParams['ALLOW'] = ($arParams["ROLE"] >= 'W');

$arParams['SHOW_GROUP_SELECTOR'] = false;
if (IsModuleInstalled('intranet')) {
    $arParams['SHOW_GROUP_SELECTOR'] = true;
}

$arParams['SHOW_DEAL_SELECTOR'] = false;
if (IsModuleInstalled('crm') && $arParams["ROLE"] == 'W') {
    $arParams['SHOW_DEAL_SELECTOR'] = true;
}

$arResult = $this->__parent->arResult;

$arParams["PULL_TAG"] = 'ALTASIB_SUPPORT_' . $arParams["ID"];
$arParams["PULL_TAG_SUPPORT"] = 'ALTASIB_SUPPORT_' . $arParams["ID"] . '_SUPPORT';
$arParams["PULL_TAG_SUPPORT_ADMIN"] = 'ALTASIB_SUPPORT_' . $arParams["ID"] . '_SUPPORT_ADMIN';

$arParams['PART_LOAD'] = false;
$arParams['MESSAGE_LIMIT'] = 5;
$arParams['HIGHLIGHT_MESSAGE_ID'] = 0;
$arParams["CREATE_BY_MESSAGE_URL"] = htmlspecialchars(CComponentEngine::makePathFromTemplate($arParams["URL_DETAIL"],
        Array("ID" => "0", "TICKET_ID" => 0, "CODE" => 0))) . '?PARRENT_MESSAGE=';
if ($arParams["ID"] > 0) {
    if ($arParams['Right']->isSupportTeam() && CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite()) {
        $rsCurrentUser = CUser::GetByID($USER->GetID());
        if (
            ($arCurrentUser = $rsCurrentUser->Fetch())
            && !empty($arCurrentUser["UF_DEPARTMENT"])
            && is_array($arCurrentUser["UF_DEPARTMENT"])
            && intval($arCurrentUser["UF_DEPARTMENT"][0]) > 0
        ) {
            $arRedirectSite = CSocNetLogComponent::GetSiteByDepartmentId($arCurrentUser["UF_DEPARTMENT"]);
            if ($arRedirectSite["LID"] != SITE_ID) {
                if ($arParams['Right']->isSupportTeam()) {
                    LocalRedirect(\ALTASIB\Support\Event::getURL($arParams['ID'], $arRedirectSite["LID"]));
                }
            }
        }
    }

    $arParams["USER_FIELDS"] = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("ALTASIB_SUPPORT", $arParams["ID"],
        LANGUAGE_ID);
    $arParams["USER_FIELDS_SHOW"] = false;

    $select = array(
        '*',
        'CATEGORY_NAME' => 'CATEGORY.NAME',
        'STATUS_NAME' => 'STATUS.NAME',
        'OWNER_USER_SHORT_NAME' => 'OWNER_USER.SHORT_NAME',
        'OWNER_USER_NAME' => 'OWNER_USER.NAME',
        'OWNER_USER_LAST_NAME' => 'OWNER_USER.LAST_NAME',
        'OWNER_USER_LOGIN' => 'OWNER_USER.LOGIN',
        'RESPONSIBLE_USER_SHORT_NAME' => 'RESPONSIBLE_USER.SHORT_NAME',
        'SLA_NAME' => 'SLA.NAME',
        'SLA_DESCRIPTION' => 'SLA.DESCRIPTION',
        'SLA_RESPONSE_TIME' => 'SLA.RESPONSE_TIME',
        'SUM_ELAPSED_TIME',
        //'GROUP_NAME' => 'GROUP.NAME',
        //'GROUP_OWNER_ID' => 'GROUP.OWNER_ID',
    );
    if (IsModuleInstalled('intranet') && Main\Loader::includeModule("socialnetwork")) {
        $select['GROUP_NAME'] = 'GROUP.NAME';
        $select['GROUP_OWNER_ID'] = 'GROUP.OWNER_ID';
    }
    foreach ($arParams["USER_FIELDS"] as $k => $v) {
        $select[] = $k;
        if (strlen($v['VALUE']) > 0) {
            $arParams["USER_FIELDS_SHOW"] = true;
        }
    }
    $obTicket = Support\TicketTable::getList(array('filter' => array('ID' => $arParams["ID"]), 'select' => $select));
    if (!$ticket = $arTicket = $obTicket->fetch()) {
        $arParams["ID"] = 0;
        $APPLICATION->AuthForm(GetMessage('ALTASIB_SUPPORT_CMP_TICKET_NOT_FOUND'));
        return false;
    } else {
        $request = Main\Context::getCurrent()->getRequest();
        $arParams['PART_LOAD'] = ($request['PART_LOAD'] == 'Y');
        if ($request->isPost() && !check_bitrix_sessid() && $request['AJAX_CALL'] == 'Y') {
            $APPLICATION->RestartBuffer();
            echo CUtil::PhpToJSObject(array('exp' => true, 'sessid' => bitrix_sessid()));
            die();
        }
        if ($request->isPost() && check_bitrix_sessid() && $request['AJAX_CALL'] == 'Y' && $arParams['PART_LOAD']) {
            $APPLICATION->RestartBuffer();
            if (!function_exists('getMessageSupport')) {
                include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/altasib/support.ticket.detail/templates/.default/message_template.php');
            }
        } elseif ($request->isPost() && check_bitrix_sessid() && $request['AJAX_CALL'] == 'Y') {
            $APPLICATION->RestartBuffer();
            require_once("ajax.php");
            die();
        }
        if (!isset($request['AJAX_CALL']) && $GLOBALS["USER"]->IsAuthorized() && CModule::IncludeModule("pull") && CPullOptions::GetNginxStatus()) {
            if (!$arParams['Right']->isSupportTeam()) {
                CPullWatch::Add($GLOBALS["USER"]->GetId(), $arParams["PULL_TAG"]);
            } else {
                if ($arParams['ROLE'] == 'W') {
                    CPullWatch::Add($GLOBALS["USER"]->GetId(), $arParams["PULL_TAG_SUPPORT_ADMIN"]);
                } else {
                    CPullWatch::Add($GLOBALS["USER"]->GetId(), $arParams["PULL_TAG_SUPPORT"]);
                }
            }
            $userShowParams = array_merge(Support\UserTable::getRow(array(
                'filter' => array('ID' => $GLOBALS["USER"]->GetId()),
                'select' => array('ID', 'LOGIN', 'SHORT_NAME')
            )), array('TICKET_ID' => $arParams["ID"]));
            CPullWatch::AddToStack($arParams["PULL_TAG_SUPPORT"],
                Array(
                    'module_id' => 'altasib.support',
                    'command' => 'showview',
                    'params' => $userShowParams
                )
            );

            CPullWatch::AddToStack($arParams["PULL_TAG_SUPPORT_ADMIN"],
                Array(
                    'module_id' => 'altasib.support',
                    'command' => 'showview',
                    'params' => $userShowParams
                )
            );
            ?>
            <script>
                BX.ready(function () {
                    BX.PULL.extendWatch('<?=CUtil::JSEscape($arParams["PULL_TAG"])?>');
                });
            </script>
            <?
        }
        if (!IsModuleInstalled('intranet')) {
            $arParams['SUPPORT_TEAM'] = Support\Tools::getSupportTeam();
        }
        //files
        $arTicket["FILES"] = array();
        $dataFile = Support\FileTable::getList(array(
            'filter' => array(
                'TICKET_ID' => $arParams['ID'],
                'MESSAGE_ID' => 0
            )
        ));
        while ($arFile = $dataFile->fetch()) {
            $arrFile = CFile::GetFileArray($arFile['FILE_ID']);
            $arrFile['URL'] = str_replace(array('#ID#', '#FILE_HASH#'), array($arParams['ID'], $arFile['HASH']),
                $arParams['URL_GET_FILE']);
            $arrFile['FORMAT_FILE_SIZE'] = CFile::FormatSize(intval($arrFile['FILE_SIZE']), 0);
            if (CFile::IsImage($arrFile['SRC'])) {
                $arTicket["FILES_IMAGE"][] = $arrFile;
            } else {
                $arTicket["FILES"][] = $arrFile;
            }
        }

        $arResult["MESSAGES"] = Array();
        $arResult['LOGGED_MESSAGES'] = array();
        $arResult['NOT_LOGGED_MESSAGES'] = array();
        $CCTP = new CTextParser();
        $CCTP->maxStringLen = 50;
        $CCTP->allow = Support\Tools::getAllowTags();

        $page = $request['page'] > 0 ? $request['page'] : 1;
        $selectMessage = array(
            '*',
            'CREATED_USER_NAME' => 'CREATED_USER.NAME',
            'CREATED_USER_LAST_NAME' => 'CREATED_USER.LAST_NAME',
            'CREATED_USER_SHORT_NAME' => 'CREATED_USER.SHORT_NAME',
        );
        $paramsTM = array(
            'order' => array('DATE_CREATE' => 'DESC', 'IS_LOG' => 'DESC'),
            'filter' => array('TICKET_ID' => $arParams['ID']),
            'select' => $selectMessage,
            //'limit' => $arParams['MESSAGE_LIMIT'],
            //'offset' => ($page-1) * $arParams['MESSAGE_LIMIT'],
        );
        $showAll = false;
        if (isset($request['message']) && (int)$request['message'] > 0) {
            $showAll = true;
            $arParams['HIGHLIGHT_MESSAGE_ID'] = (int)$request['message'];
        }

        if (isset($request['LOAD_FULL']) && $request['LOAD_FULL'] == 'Y') {
            $showAll = true;
        }

        if (!$showAll && !$arParams['PART_LOAD']) {
            $lastId = \ALTASIB\Support\MessageReadTable::getLastUnreadId($arParams['ID']);
            $paramsTM['filter']['>=ID'] = $lastId;
            $showAll = true;
        }

        if (!$showAll) {
            $paramsTM['limit'] = $arParams['MESSAGE_LIMIT'];
            $paramsTM['offset'] = ($page - 1) * $arParams['MESSAGE_LIMIT'];
        }

        if (!$arParams['IS_SUPPORT_TEAM']) {
            $paramsTM['filter']['IS_HIDDEN'] = 'N';
        }
        $result = Support\TicketMessageTable::getList($paramsTM);
        $result = new CDBResult($result);
        if (!$showAll) {
            $result->NavStart($arParams['MESSAGE_LIMIT']);
        }

        while ($arMessage = $result->Fetch()) {
            $arResult["MESSAGES"][] = $arMessage;
            
            if ($arMessage['IS_LOG'] == 'N') {
	            $arResult['NOT_LOGGED_MESSAGES'][] = $arMessage;
            } else {
	            $arResult['LOGGED_MESSAGES'][] = $arMessage;
            }
        }
        $arResult["MESSAGES"] = array_reverse($arResult["MESSAGES"]);

        /**
         * favorite
         */

        $arResult['FAVORITE_MESSAGES'] = array();
        $arResult['LOGGED_FAVORITE_MESSAGES'] = array();
        $arResult['NOT_LOGGED_FAVORITE_MESSAGES'] = array();
        $obFav = \ALTASIB\Support\FavoriteTable::getList(array(
            'filter' => array(
                'TICKET_ID' => $arParams['ID'],
                'USER_ID' => $USER->GetID()
            )
        ));
        while ($favorite = $obFav->fetch()) {
        	$arFavMessage = array_merge(array('FAVORITE' => 'Y'),
                \ALTASIB\Support\TicketMessageTable::getRow(array(
                    'filter' => array('ID' => $favorite['MESSAGE_ID']),
                    'select' => $selectMessage
                )));
        
        	$arResult['FAVORITE_MESSAGES'][] = $arFavMessage;
            
            if ($arFavMessage['IS_LOG'] == 'N') {
            	$arResult['NOT_LOGGED_FAVORITE_MESSAGES'][] = $arFavMessage;
            } else {
            	$arResult['LOGGED_FAVORITE_MESSAGES'][] = $arFavMessage;
            }
        }

        $addCnt = 0;
        if (!$arParams['PART_LOAD']) {
            $newLastId = $arResult["MESSAGES"][count($arResult["MESSAGES"]) - $arParams['MESSAGE_LIMIT']]['ID'];
            if ($newLastId > $lastId && ($arResult["MESSAGES"][count($arResult["MESSAGES"]) - $arParams['MESSAGE_LIMIT']]['CREATED_USER_ID'] != $USER->GetID() || $lastId == 0)) {
                \ALTASIB\Support\MessageReadTable::setLastUnreadId(array(
                    'TICKET_ID' => $arParams['ID'],
                    'LAST_MESSAGE_ID' => $newLastId,
                    'USER_ID' => $USER->GetID(),
                    'READ_DATE' => new Bitrix\Main\Type\DateTime(date('Y-m-d H:i:s'), 'Y-m-d H:i:s')
                ));
            }
            unset($paramsTM['filter']['>=ID']);
            if ($newLastId > 0) {
                $paramsTM['filter']['<ID'] = $newLastId;
            }

            if ($lastId == 0) {
                $addCnt -= count($arResult["MESSAGES"]);
            } else {
                $addCnt = count($arResult["MESSAGES"]);
            }

            $showAll = false;
        }
        if (!$showAll) {
            $countQuery = new Bitrix\Main\Entity\Query(Support\TicketMessageTable::getEntity());
            $countQuery
                ->registerRuntimeField("CNT", array(
                        "data_type" => "integer",
                        "expression" => array("COUNT(1)")
                    )
                )
                ->setSelect(array("CNT"))
                ->setFilter($paramsTM['filter']);
            $totalCnt = $countQuery->setLimit(null)->setOffset(null)->exec()->fetch();
            $totalCount = intval($totalCnt['CNT']) + $addCnt;
            if ($totalCount <= 0) {
                $totalCount = intval($totalCnt['CNT']);
            }
            $arResult['MESSAGE_CNT'] = $totalCount;

            $totalPage = ceil($totalCount / $arParams['MESSAGE_LIMIT']);
            $result->NavRecordCount = $totalCount;
            $result->NavPageCount = $totalPage;
            $result->NavPageNomer = $page;
            $arResult["NAV_PARAMS"] = $result->GetNavParams();
            $arResult["NAV_NUM"] = $result->NavNum;
            $result->bShowAll = false;
            $arResult["NAV_OBJECT"] = $result;
            $arResult['leftCnt'] = $totalCount - ($page * $arParams['MESSAGE_LIMIT']);
        } else {
            $arResult['MESSAGE_CNT'] = 1;
        }
    }

    if ($arParams['PART_LOAD']) {
        $mlist = '';
        $lastDate = "";
        foreach ($arResult["MESSAGES"] as $arMessage) {
            $mlist .= getMessageSupport($arMessage, $arParams, $arTicket, $lastDate);
            $lastDate = $arMessage['DATE_CREATE'];
        }
        $loadResult = array(
            'status' => true,
            'html' => $mlist,
            'end' => ($totalPage == $page),
            'totalCount' => $totalCount,
            'leftCnt' => $totalCount - ($page * $arParams['MESSAGE_LIMIT'])
        );
        if ($showAll) {
            $loadResult['end'] = $showAll;
        }
        echo CUtil::PhpToJSObject($loadResult);
        die();
    }
    $arTicket['MESSAGE'] = $CCTP->convertText($arTicket["MESSAGE"]);
    $arTicket["PRIORITY_NAME"] = $arTicket['PRIORITY_ID'] > 0 ? Support\Priority::getName($arTicket['PRIORITY_ID']) : '';
    $arResult["TICKET_INFO"] = $arTicket;

    $dataMember = Support\TicketMemberTable::getList(array(
        'select' => array(
            '*',
            'USER_SHORT_NAME' => 'USER.SHORT_NAME'
        ),
        'filter' => array('TICKET_ID' => $arParams['ID'])
    ));
    while ($member = $dataMember->fetch()) {
        $arResult['MEMBERS'][$member['USER_ID']] = $member;
    }

    if ($arParams['SHOW_DEAL_SELECTOR'] && $USER->IsAdmin() && CModule::IncludeModule('crm')) {
        $arResult['CRM']['CONTACT'] = array();
        $arResult['CRM']['COMPANY'] = array();
        $arResult['CRM']['DEAL_LIST'] = array();
        $obContact = CCrmContact::GetList(array(), array('UF_SUPPORT_USER_ID' => $arTicket['OWNER_USER_ID']));
        if ($arResult['CRM']['CONTACT'] = $obContact->fetch()) {
            $arResult['CRM']['CONTACT']['DETAIL_URL'] = CComponentEngine::makePathFromTemplate(
                COption::GetOptionString('crm', 'path_to_contact_show'),
                array(
                    'contact_id' => $arResult['CRM']['CONTACT']['ID']
                )
            );
            if ($arResult['CRM']['CONTACT']['COMPANY_ID'] > 0) {
                $obCompany = CCrmCompany::GetList(array(), array('ID' => $arResult['CRM']['CONTACT']['COMPANY_ID']));
                if ($arResult['CRM']['COMPANY'] = $obCompany->Fetch()) {
                    $arResult['CRM']['COMPANY']['DETAIL_URL'] = CComponentEngine::makePathFromTemplate(
                        COption::GetOptionString('crm', 'path_to_company_show'),
                        array(
                            'company_id' => $arResult['CRM']['COMPANY']['ID']
                        )
                    );

                    $obDealList = CCrmDeal::GetList(array(), array('COMPANY_ID' => $arResult['CRM']['COMPANY']['ID']));
                    while ($deal = $obDealList->Fetch()) {
                        $arResult['CRM']['DEAL_LIST'][$deal['ID']] = $deal;
                    }
                }
            }
        }
    }
    
    $userId = $USER->getId();
    $responsibleId = $arTicket['OWNER_USER_ID'];
    $arResult['OWNER_USER_ID'] = $responsibleId;
   
    $categoryDB = ALTASIB\Support\CategoryTable::getList();
    $categoryArray = array();
    while ($category = $categoryDB->fetch()) {
        $categoryArray[$category['ID']] = $category['DESCRIPTION'];
    }
    
    $isSectionAdmin = 0;
    if ($categoryArray[$arTicket['CATEGORY_ID']] == 'IT') {
        
        $isSectionAdmin = ( in_array( 19, CUser::GetUserGroup($userId) ) );
        
    } elseif ($categoryArray[$arTicket['CATEGORY_ID']] == 'AXO') {
        
        $isSectionAdmin = ( in_array( 20, CUser::GetUserGroup($userId) ) );
        
    } elseif ($categoryArray[$arTicket['CATEGORY_ID']] == 'HR') {
        
        $isSectionAdmin = ( in_array( 21, CUser::GetUserGroup($userId) ) );
        
    }
    
    $isInSection = 0;
    if ($categoryArray[$arTicket['CATEGORY_ID']] == 'IT') {
        
        $isInSection = ( in_array( 16, CUser::GetUserGroup($userId) ) || in_array( 19, CUser::GetUserGroup($userId) ) );
        
    } elseif ($categoryArray[$arTicket['CATEGORY_ID']] == 'AXO') {
        
        $isInSection = ( in_array( 17, CUser::GetUserGroup($userId) ) || in_array( 20, CUser::GetUserGroup($userId) ) );
        
    } elseif ($categoryArray[$arTicket['CATEGORY_ID']] == 'HR') {
        
        $isInSection = ( in_array( 18, CUser::GetUserGroup($userId) ) || in_array( 21, CUser::GetUserGroup($userId) ) );
    }
    
    $arResult['isInSection']    = $isInSection;
    $arResult['isSectionAdmin'] = $isSectionAdmin;
    
    $arResult['USER_IS_OWNER'] = ($userId == $arResult['OWNER_USER_ID']);
    
    
    
//     echo '<pre>';
//     print_r($_POST);
//     echo '</pre>';

//     print_r($arResult['isSectionAdmin']);
//     echo '<br>';
//     print_r($arTicket['CATEGORY_ID']);
//     echo '<br>';
//     print_r($categoryArray);
//     echo '<br>';
//     print_r($arResult['isSectionAdmin']);
//     echo '</pre>';

    $arResult['rate_1'] = '';
    $arResult['rate_2'] = '';
    $arResult['rate_3'] = '';
    
    if (isset($_GET['change-rate']) && $arResult['USER_IS_OWNER'] && in_array($_GET['change-rate'], array(1,2,3))) {
    		$result = Support\TicketTable::update($arTicket["ID"],array('MARK_VALUE'=>$_GET['change-rate']));
    		if ($result->isSuccess()) {
    			$arTicket['MARK_VALUE'] = $_GET['change-rate'];
    			
    			Support\TicketMessageTable::add(Array(
    			    'CREATED_USER_ID' => $userId,
    			    'TICKET_ID' => $arTicket['ID'],
    			    'MESSAGE' => "Изменена оценка. Текущая - {$_GET['change-rate']}",
    			    'IS_LOG' => 'Y'
			    ));
    		}
    }
    
    if (!empty($arTicket['MARK_VALUE'])) {
    	switch ($arTicket['MARK_VALUE']) {
    		case 1:
    			$arResult['rate_1'] = 'checked';
    			break;
    		case 2:
    			$arResult['rate_2'] = 'checked';
    			break;
    		case 3:
    			$arResult['rate_3'] = 'checked';
    			break;
    	}
    }
    

    $arResult['EXTENDED_MENU'] = 0;
    if ($arResult['TICKET_INFO']['RESPONSIBLE_USER_ID'] == $userId || $isSectionAdmin) {
        $arResult['EXTENDED_MENU'] = 1;
    }
    
    
    $departmentStructure = CIntranetUtils::GetStructure();
    $userDepartmentId = array_shift(CIntranetUtils::GetUserDepartments($responsibleId));
    $userHead = $departmentStructure['DATA'][$userDepartmentId]['UF_HEAD'];
    
//     echo "<h1>userId = $userId</h1>";
//     echo "<h1>responsibleId = $responsibleId</h1>";
//     echo "<h1>userHead = $userHead</h1>";

//     echo '<pre>'; print_r($_GET); echo '</pre>';

    $statusCompletedId = 3;
    $arResult['IS_CLOSED_STATUS'] = $arTicket['STATUS_ID'] == $statusCompletedId ? 1 : 0;
    if (isset($_POST['close-ticket']) && ( $arResult['USER_IS_OWNER'] || $arResult['EXTENDED_MENU'] ) && !$arResult['IS_CLOSED_STATUS']) {
        if ($_POST['close-ticket'] == 1) {
		    $result = Support\TicketTable::update($arTicket["ID"],array('STATUS_ID'=>$statusCompletedId));
		    if ($result->isSuccess()) {
		    	$arResult['IS_CLOSED_STATUS'] = 1;
		    	
		    	$mark = "[list=1]
[*]<a href='/support/ticket/{$arTicket['ID']}/?change-rate=1'>Так себе</a>
[*]<a href='/support/ticket/{$arTicket['ID']}/?change-rate=2'>Нормальный</a>
[*]<a href='/support/ticket/{$arTicket['ID']}/?change-rate=3'>Хороший</a>
[/list]";
		    	
		    	$notifyHeadRes = \CIMNotify::add(array(
		    			'FROM_USER_ID' => $arTicket['RESPONSIBLE_USER_ID'],
		    			'TO_USER_ID' => $arTicket['OWNER_USER_ID'],
		    			"NOTIFY_TYPE" => IM_NOTIFY_FROM,
		    			"NOTIFY_MODULE" => "altasib.support",
		    			"NOTIFY_EVENT" => 'support_new',
		    			"NOTIFY_ANSWER" => "Y",
		    			"NOTIFY_TAG" => "ALTASIB|SUPPORT|TICKET|" . $arTicket['ID'],
		    			'NOTIFY_MESSAGE' => "Дайте оценку к тикету <a href='/support/ticket/{$arTicket['ID']}/'>#{$arTicket['ID']}</a> : {$mark} ",
    			        'NOTIFY_MESSAGE_OUT' => "Дайте оценку к тикету <a href='/support/ticket/{$arTicket['ID']}/'>#{$arTicket['ID']}</a> : {$mark} ",
    			        "NOTIFY_BUTTONS" => $buttons,
		    	));
		    }
    	}
    }
    
    $arResult['CAN_CONFIRM'] = 0;
    if ($userHead == $userId && $categoryArray[$arTicket['CATEGORY_ID']] == 'AXO' && $arTicket['IS_CONFIRMED'] == 0) {
    	
        $arResult['CAN_CONFIRM'] = 1;
    	
    	if (isset($_GET['confirm'])) {
    		if ($_GET['confirm'] == 1) {
    			$result = Support\TicketTable::update($arTicket['ID'], array(
    					'IS_CONFIRMED' => 1,
    			));
    			$arResult['CAN_CONFIRM'] = 0;
    			
    			$group = CGroup::GetListEx(Array(),Array(),0,0,Array('*'));
    			$groupsArray = array();
    			while($record = $group->fetch() ) {
    			    $groupsArray[$record['ID']][] = $record['USER_USER_ID'];
    			}
    			
//     			echo '<pre>'; print_r($groupsArray[17]); echo '</pre>';
    			$notifyRes = [];
    			foreach ($groupsArray[17] as $cuserId) {

    				$message = array(
    			        'FROM_USER_ID' => $arTicket['OWNER_USER_ID'],
    			        'TO_USER_ID' => $cuserId,
    			        "NOTIFY_TYPE" => IM_NOTIFY_FROM,
    			        "NOTIFY_MODULE" => "altasib.support",
    			        "NOTIFY_EVENT" => 'support_new',
    			        "NOTIFY_ANSWER" => "Y",
    			        "NOTIFY_TAG" => "ALTASIB|SUPPORT|TICKET|" . $arTicket['ID'],
    			        'NOTIFY_MESSAGE' => "Новое обращение #{$arTicket['ID']}. Тема: <a href='/support/ticket/{$arTicket['ID']}/'>{$arTicket['TITLE']}</a>. Текст обращения: {$arTicket['MESSAGE']} ",
    			        'NOTIFY_MESSAGE_OUT' => "Новое обращение #{$arTicket['ID']}. Тема: <a href='/support/ticket/{$arTicket['ID']}/'>{$arTicket['TITLE']}</a>. Текст обращения: {$arTicket['MESSAGE']} ",
    			    );
    				
//     				echo '<pre>'; print_r($message); echo '</pre>';
    				
    			    $notifyRes[] = \CIMNotify::add($message);
    			}
    			
//     			echo '<pre>'; print_r($notifyRes); echo '</pre>';
    			
    		}
    		
    		if ($_GET['confirm'] == 0) {
    			$result = Support\TicketTable::update($arTicket['ID'], array(
    					'IS_CONFIRMED' => -1,
    			));
    			$arResult['CAN_CONFIRM'] = 0;
    			
    			$message = array(
    					'FROM_USER_ID' => $USER->getId(),
    					'TO_USER_ID' => $arTicket['OWNER_USER_ID'],
    					"NOTIFY_TYPE" => IM_NOTIFY_FROM,
    					"NOTIFY_MODULE" => "altasib.support",
    					"NOTIFY_EVENT" => 'support_new',
    					"NOTIFY_ANSWER" => "Y",
    					"NOTIFY_TAG" => "ALTASIB|SUPPORT|TICKET|" . $arTicket['ID'],
    					'NOTIFY_MESSAGE' => "Обращение <a href='/support/ticket/{$arTicket['ID']}/'>{$arTicket['TITLE']}</a> отклонено",
    					'NOTIFY_MESSAGE_OUT' => "Обращение <a href='/support/ticket/{$arTicket['ID']}/'>{$arTicket['TITLE']}</a> отклонено",
    			);
    			$notifyRes = \CIMNotify::add($message);
    			
    		}
    	}
    	
    	
    }
    
    $filter = Array();
    $usersDB = CUser::GetList(($by = "NAME"), ($order = "asc"), $filter);
    while($user = $usersDB->fetch())
    {
        $arResult['ALL_USERS'][$user['ID']] = ($user['NAME'] || $user['LAST_NAME']) ? $user['NAME'] . ' ' . $user['LAST_NAME'] : (($user['LOGIN']) ? $user['LOGIN'] : ((($user['EMAIL']) ? $user['EMAIL'] : 'Сотрудник#' . $user['ID'])));
    }
    
    $arResult['HAS_GROUP'] = $arTicket['GROUP_ID'];
    $arResult['TIKET'] = $arTicket;
    
//     echo '<pre>';
//     print_r($arResult['TIKET']);
//     echo '</pre>'; die;
    
    if (!empty($_POST['task_responsible']) && $arTicket['GROUP_ID'] && Main\Loader::includeModule("tasks")) {
        
                    $taskTitle = "Техподдержка Тикет #" . $arTicket['ID'];
                    $arFields = Array(
                        "TITLE" => $taskTitle,
                        "DESCRIPTION" => "Тикет - <a href='https://{$_SERVER['SERVER_NAME']}/support/ticket/{$arTicket['ID']}/'>$taskTitle</a><br>{$_POST['task_description']}",
                        "CREATED_BY" => $userId,
                        "RESPONSIBLE_ID" => $_POST['task_responsible'],
                        "GROUP_ID" => $arTicket['GROUP_ID'],
                        );
                    
                    $obTask = new CTasks;
                    $taskID = $obTask->Add($arFields);
                    

                    if ($taskID) {
                        $notifyHeadRes = \CIMNotify::add(array(
                            'FROM_USER_ID' => $userId,
                            'TO_USER_ID' => $_POST['task_responsible'],
                            "NOTIFY_TYPE" => IM_NOTIFY_FROM,
                            "NOTIFY_MODULE" => "altasib.support",
                            "NOTIFY_EVENT" => 'support_new',
                            "NOTIFY_ANSWER" => "Y",
                            "NOTIFY_TAG" => "ALTASIB|SUPPORT|TICKET|TASK|" . $arTicket['ID'],
                            'NOTIFY_MESSAGE' => "Создано задание <a href='/workgroups/group/{$arTicket['GROUP_ID']}/tasks/task/view/$taskID/'>$taskTitle</a>",
                            'NOTIFY_MESSAGE_OUT' => "Создано задание <a href='/workgroups/group/{$arTicket['GROUP_ID']}/tasks/task/view/$taskID/'>$taskTitle</a>",
                            "NOTIFY_BUTTONS" => $buttons,
                        ));
                    }
                    
                    $res1 = Support\Tools::taskPlanner($taskID, $_POST['task_responsible']);
                    $res2 = Support\TicketTable::update($arTicket['ID'], array('TASK_ID' => $taskID));
                    
//                     echo '<pre>';
//                     var_dump($res1);
//                     var_dump($res2);
//                     echo '</pre>';
    }
    
    
    $this->IncludeComponentTemplate();

    $APPLICATION->SetTitle(GetMessage('ALTASIB_SUPPORT_TICKET_TITLE',
            array('#TICKET_ID#' => $arParams['ID'])) . ' - ' . $ticket['TITLE']);
}
$APPLICATION->AddHeadScript('/bitrix/js/main/utils.js');
CJSCore::Init(array('access', 'window', 'jquery', 'viewer'));
?>