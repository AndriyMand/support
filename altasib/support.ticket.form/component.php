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

function getFileTxtLine($TICKET_ID, $MESSAGE_ID, $SITE_ID)
{
	$fileTxt = '';
	$dataFile = Support\FileTable::getList(array(
			'filter' => array(
					'TICKET_ID' => $TICKET_ID,
					'MESSAGE_ID' => $MESSAGE_ID
			)
	));
	while ($file = $dataFile->fetch()) {
		$fileUrl = str_replace(array('#ID#', '#FILE_HASH#'), array($TICKET_ID, $file['HASH']),
				\COption::GetOptionString('altasib.support', 'path_file', '', $SITE_ID));
		$http = \CMain::IsHTTPS() ? "https://" : "http://";
		$fileUrl = $http . $_SERVER['SERVER_NAME'] . $fileUrl;

		if ($fileArr = \CFile::GetFileArray($file['FILE_ID'])) {
			$fileTxt .= '<a href="' . $fileUrl . '">' . $fileArr['ORIGINAL_NAME'] . '</a><br>';
		}
	}
	if (strlen($fileTxt) > 0) {
		$fileTxt = Loc::getMessage('ALTASIB_SUPPORT_EVENT_FILES') . $fileTxt;
	}
	return $fileTxt;
}

function getURL($TICKET_ID, $SITE_ID)
{
	$url = str_replace('#ID#', $TICKET_ID,
			\COption::GetOptionString('altasib.support', 'path_detail', '', $SITE_ID));
	$http = \CMain::IsHTTPS() ? "https://" : "http://";
	return $http . $_SERVER['SERVER_NAME'] . $url;
}

function getEventDataEx($TICKET_ID, $MESSAGE_ID = 0)
{
	$obData = Support\TicketTable::getList(array(
			'filter' => array('ID' => $TICKET_ID),
			'select' => array(
					'*',
					'CATEGORY_NAME' => 'CATEGORY.NAME',
					'STATUS_NAME' => 'STATUS.NAME',
					'SLA_NAME' => 'SLA.NAME',

					'OWNER_USER_NAME' => 'OWNER_USER.NAME',
					'OWNER_USER_LOGIN' => 'OWNER_USER.LOGIN',
					'OWNER_USER_EMAIL' => 'OWNER_USER.EMAIL',
					'OWNER_USER_SHORT_NAME' => 'OWNER_USER.SHORT_NAME',
					'OWNER_USER_LIST_NAME' => 'OWNER_USER.LIST_NAME',

					'CREATED_USER_NAME' => 'CREATED_USER.NAME',
					'CREATED_USER_LOGIN' => 'CREATED_USER.LOGIN',
					'CREATED_USER_EMAIL' => 'CREATED_USER.EMAIL',
					'CREATED_USER_SHORT_NAME' => 'CREATED_USER.SHORT_NAME',
					'CREATED_USER_LIST_NAME' => 'CREATED_USER.LIST_NAME',

					'MODIFIED_USER_NAME' => 'MODIFIED_USER.NAME',
					'MODIFIED_USER_LOGIN' => 'MODIFIED_USER.LOGIN',
					'MODIFIED_USER_EMAIL' => 'MODIFIED_USER.EMAIL',
					'MODIFIED_USER_SHORT_NAME' => 'MODIFIED_USER.SHORT_NAME',
					'MODIFIED_USER_LIST_NAME' => 'MODIFIED_USER.LIST_NAME',

					'RESPONSIBLE_USER_NAME' => 'RESPONSIBLE_USER.NAME',
					'RESPONSIBLE_USER_LOGIN' => 'RESPONSIBLE_USER.LOGIN',
					'RESPONSIBLE_USER_EMAIL' => 'RESPONSIBLE_USER.EMAIL',
					'RESPONSIBLE_USER_SHORT_NAME' => 'RESPONSIBLE_USER.SHORT_NAME',
					'RESPONSIBLE_USER_LIST_NAME' => 'RESPONSIBLE_USER.LIST_NAME'
			)
	)
			);
	if ($data = $obData->fetch()) {
		$arEvent = array(
				'TICKET_ID' => $TICKET_ID,
				'TICKET_TITLE' => $data['TITLE'],
				'SITE_ID' => $data['SITE_ID'],
				'TICKET_MESSAGE' => $data['MESSAGE'],
				'TICKET_DATE_CREATE' => $data['DATE_CREATE']->toString(),
				'TICKET_CATEGORY' => $data['CATEGORY_NAME'],
				'TICKET_STATUS' => $data['STATUS_NAME'],
				'TICKET_PRIORITY_ID' => $data['PRIORITY_ID'],
				'TICKET_PRIORITY' => $data['PRIORITY_ID'] > 0 ? Support\Priority::getName($data['PRIORITY_ID']) : '',
				'TICKET_SLA' => $data['SLA_NAME'],
				'TICKET_IS_CLOSE' => $data['IS_CLOSE'],

				'TICKET_OWNER_USER_ID' => $data['OWNER_USER_ID'],
				'TICKET_OWNER_USER_NAME' => $data['OWNER_USER_NAME'],
				'TICKET_OWNER_USER_LOGIN' => $data['OWNER_USER_LOGIN'],
				'TICKET_OWNER_USER_EMAIL' => $data['OWNER_USER_EMAIL'],
				'TICKET_OWNER_USER_SHORT_NAME' => $data['OWNER_USER_SHORT_NAME'],
				'TICKET_OWNER_USER_LIST_NAME' => $data['OWNER_USER_LIST_NAME'],

				'TICKET_CREATED_USER_ID' => $data['CREATED_USER_ID'],
				'TICKET_CREATED_USER_NAME' => $data['CREATED_USER_NAME'],
				'TICKET_CREATED_USER_LOGIN' => $data['CREATED_USER_LOGIN'],
				'TICKET_CREATED_USER_EMAIL' => $data['CREATED_USER_EMAIL'],
				'TICKET_CREATED_USER_SHORT_NAME' => $data['CREATED_USER_SHORT_NAME'],
				'TICKET_CREATED_USER_LIST_NAME' => $data['CREATED_USER_LIST_NAME'],

				'TICKET_MODIFIED_USER_ID' => $data['MODIFIED_USER_ID'],
				'TICKET_MODIFIED_USER_NAME' => $data['MODIFIED_USER_NAME'],
				'TICKET_MODIFIED_USER_LOGIN' => $data['MODIFIED_USER_LOGIN'],
				'TICKET_MODIFIED_USER_EMAIL' => $data['MODIFIED_USER_EMAIL'],
				'TICKET_MODIFIED_USER_SHORT_NAME' => $data['MODIFIED_USER_SHORT_NAME'],
				'TICKET_MODIFIED_USER_LIST_NAME' => $data['MODIFIED_USER_LIST_NAME'],

				'TICKET_RESPONSIBLE_USER_ID' => $data['RESPONSIBLE_USER_ID'],
				'TICKET_RESPONSIBLE_USER_NAME' => $data['RESPONSIBLE_USER_NAME'],
				'TICKET_RESPONSIBLE_USER_LOGIN' => $data['RESPONSIBLE_USER_LOGIN'],
				'TICKET_RESPONSIBLE_USER_EMAIL' => $data['RESPONSIBLE_USER_EMAIL'],
				'TICKET_RESPONSIBLE_USER_SHORT_NAME' => $data['RESPONSIBLE_USER_SHORT_NAME'],
				'TICKET_RESPONSIBLE_USER_LIST_NAME' => $data['RESPONSIBLE_USER_LIST_NAME'],

				'TICKET_FILES' => getFileTxtLine($TICKET_ID, 0, $data['SITE_ID']),

				'SUPPORT_EMAIL' => \COption::GetOptionString('altasib.support', 'SUPPORT_MAIL'),
				'URL' => getURL($data['ID'], $data['SITE_ID']),
		);
		
		

		if ($MESSAGE_ID > 0) {
			$obDataMessage = TicketMessageTable::getList(array(
					'filter' => array(
							'TICKET_ID' => $TICKET_ID,
							'ID' => $MESSAGE_ID
					),
					'select' => array(
							'CREATED_USER_NAME' => 'CREATED_USER.NAME',
							'CREATED_USER_LOGIN' => 'CREATED_USER.LOGIN',
							'CREATED_USER_EMAIL' => 'CREATED_USER.EMAIL',
							'CREATED_USER_SHORT_NAME' => 'CREATED_USER.SHORT_NAME',
							'CREATED_USER_LIST_NAME' => 'CREATED_USER.LIST_NAME',
							'ELAPSED_TIME',
					)
			));
			if ($message = $obDataMessage->fetch()) {
				$arEvent = array_merge($arEvent, $message);
			}
		}
		return $arEvent;
	} else {
		return false;
	}
}

if (!Main\Loader::includeModule("altasib.support")) {
    ShowError("ALTASIB_SUPPORT_MODULE_NOT_INSTALL");
    return;
}
$arParams["ID"] = (int)$arParams["ID"];

if (!($arParams['Right'] instanceof ALTASIB\Support\Rights)) {
    $arParams['Right'] = new ALTASIB\Support\Rights($USER->GetID(), $arParams['ID']);
}

$arParams["ROLE"] = $Role = $arParams['Right']->getRole();
if ($arParams['Right']->getRole() == 'D') {
    $APPLICATION->AuthForm('');
}

$arParams['ALLOW'] = ($arParams["ROLE"] >= 'W');
$arParams['IS_SUPPORT_TEAM'] = $arParams['Right']->isSupportTeam();
$arParams["REPLY_ON"] = COption::GetOptionString("altasib.support", "REPLY_ON", "Y");

$arResult = $this->__parent->arResult;

$arParams["USER_FIELDS"] = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("ALTASIB_SUPPORT", 0, LANGUAGE_ID);

$arParams["PULL_TAG"] = 'ALTASIB_SUPPORT_' . $arParams["ID"];
$arParams["PULL_TAG_SUPPORT"] = 'ALTASIB_SUPPORT_' . $arParams["ID"] . '_SUPPORT';
$arParams["PULL_TAG_SUPPORT_ADMIN"] = 'ALTASIB_SUPPORT_' . $arParams["ID"] . '_SUPPORT_ADMIN';
$arParams['SHOW_FULL_FORM'] = ($arParams['SHOW_FULL_FORM'] == 'Y');

$request = Main\Context::getCurrent()->getRequest();

$ajax = false;
if (isset($request['SUPPORT_AJAX']) && $request['SUPPORT_AJAX'] == 'Y') {
    $ajax = true;
    $APPLICATION->RestartBuffer();
    CUtil::JSPostUnescape();
}
//edit check
if ($request->isPost() && check_bitrix_sessid() && isset($request['edit_support_message_id']) && $request['edit_support_message_id'] > 0 && isset($request['check_support_message_edit'])) {
    $arParams['edit_support_message_id'] = (int)$request['edit_support_message_id'];
    $message = Support\TicketMessageTable::getRowById($arParams['edit_support_message_id']);
    CTimeZone::Disable();
    if ($arParams['ALLOW'] || ($message['CREATED_USER_ID'] == $USER->GetID() && (time() < AddToTimeStamp(array('MI' => 5),
                    MakeTimeStamp($message['DATE_CREATE'], "DD.MM.YYYY HH:MI:SS"))))
    ) {
        echo CUtil::PhpToJSObject(array('result' => true));
    } else {
        echo CUtil::PhpToJSObject(array('result' => false));
    }

    die();
}
//edit
if ($request->isPost() && check_bitrix_sessid() && isset($request['edit_support_message_id']) && $request['edit_support_message_id'] > 0) {
    $editProcess = isset($request['ACTION']);
    if ($editProcess) {
        $FILES = $_POST["FILES"];
    }

    $arParams['edit_support_message_id'] = (int)$request['edit_support_message_id'];
    $message = Support\TicketMessageTable::getRowById($arParams['edit_support_message_id']);

    CTimeZone::Disable();
    if (!$arParams['ALLOW'] && time() > AddToTimeStamp(array('MI' => 5),
            MakeTimeStamp($message['DATE_CREATE'], "DD.MM.YYYY HH:MI:SS"))
    ) {
        return;
    }
    CTimeZone::Enable();

    if (!$arParams['ALLOW'] && $message['CREATED_USER_ID'] != $USER->GetID() && !$arParams['IS_SUPPORT_TEAM']) {
        return;
    } else {
        if (!$editProcess) {
            $arResult['MESSAGE'] = $message;
        }

        $files = Support\FileTable::getList(array('filter' => array('MESSAGE_ID' => $arParams['edit_support_message_id'])));
        while ($arFile = $files->fetch()) {
            $arResult['FILES_EDIT_VALUE'][] = $arFile['FILE_ID'];
            if ($editProcess) {
                $keyDelFile = array_search($arFile['FILE_ID'], $FILES);
                if ($keyDelFile !== false) {
                    unset($FILES[$keyDelFile]);
                }

                if (!CFile::GetFileArray($arFile['FILE_ID'])) {
                    Support\FileTable::delete($arFile['ID']);
                }
            }
        }

        if ($editProcess) {
            $dataUpdate = array(
                'TICKET_ID' => (int)$request["TICKET_ID"],
                'MESSAGE' => $_POST['MESSAGE_e'],
                'FILES' => $FILES,
            );
            $updRes = Support\TicketMessageTable::update($arParams['edit_support_message_id'], $dataUpdate);
            if ($updRes->isSuccess()) {
                ?>
                <script bxrunfirst="true">
                    top.BX.WindowManager.Get().Close();
                    top.BX.showWait();
                    top.BX.reload('<?=CUtil::JSEscape(str_replace('#ID#', $request["TICKET_ID"],
                        $arParams['URL_DETAIL']));?>', true);
                </script>
                <?
                die();
            } else {
                $arResult["ERRORS"] = $updRes->getErrorMessages();
            }
        }
    }
}

$arResult["section"] = !empty($request['section']) ? $request['section'] : '';



// process POST
if ($request->isPost() && check_bitrix_sessid() && (!empty($request['SEND_MESSAGE']) || !empty($request["t_submit"]) || !empty($request["t_submit_go"])) && ($arParams["HAVE_ANSWER"] || $arParams['ID'] == 0 && $arParams["HAVE_CREATE"])) {
    $TICKET_ID = (int)$request["TICKET_ID"];
    if ($TICKET_ID == 0) {
        //UF
        $arUserFields = Array();
        foreach ($arParams["USER_FIELDS"] as $FIELD_NAME => $arPostField) {

            if ($arPostField["EDIT_IN_LIST"] == "Y") {
                if ($arPostField["USER_TYPE"]["BASE_TYPE"] == "file") {
                    $arUserFields[$arPostField["FIELD_NAME"]] = $_FILES[$arPostField["FIELD_NAME"]];
                    $arUserFields[$arPostField["FIELD_NAME"]]["del"] = $_POST[$arPostField["FIELD_NAME"] . "_del"];
                    $arUserFields[$arPostField["FIELD_NAME"]]["old_id"] = $old_id;
                } else {
                    $arUserFields[$arPostField["FIELD_NAME"]] = $_POST[$arPostField["FIELD_NAME"]];
                }
            }
        }
        
//         echo '<pre>';
//         print_r($request);
//         echo '</pre>';
        $categoryId = !empty($request["CATEGORY_ID_3"]) ? (int)$request["CATEGORY_ID_3"] : (!empty($request["CATEGORY_ID_2"]) ? (int)$request["CATEGORY_ID_2"] : (int)$request["CATEGORY_ID_1"]);
        

        $arTicket = Array(
            'TITLE' => $request["TITLE"],
            'CATEGORY_ID' => (int)$categoryId,
            'PRIORITY_ID' => (int)$request["PRIORITY_ID"],
            'MESSAGE' => $_POST['MESSAGE'],
            'FILES' => $request["FILES"],
        );

        if ($arParams['ALLOW'] && (int)$request['PARRENT_MESSAGE_ID'] > 0) {
            $parentMessage = Support\TicketMessageTable::getRow(array(
                'filter' => array('ID' => $request['PARRENT_MESSAGE_ID']),
                'select' => array('TICKET_OWNER_USER_ID' => 'TICKET.OWNER_USER_ID')
            ));

            $arTicket['OWNER_USER_ID'] = $parentMessage['TICKET_OWNER_USER_ID'];
        } else {
            if ($arParams['ALLOW'] && IsModuleInstalled('intranet') && is_array($request['OWNER_ID']) && count($request['OWNER_ID']) > 0) {
                $arTicket['OWNER_USER_ID'] = array_shift($request['OWNER_ID']);
            } elseif (($arParams['ALLOW'] || Support\ClientTable::getList(array('filter' => array('RESPONSIBLE_USER_ID' => $USER->GetID())))) && (int)$request['OWNER_ID'] > 0) {
                $arTicket['OWNER_USER_ID'] = (int)$request['OWNER_ID'];
            }
        }
        
        $arTicket['RESPONSIBLE_USER_ID'] = -1;
        
        $needToConfirm = 0;
        
        
        $categoryDB = ALTASIB\Support\CategoryTable::getList();
        $categoryArray = array();
        while ($category = $categoryDB->fetch()) {
            $categoryArray[$category['ID']] = $category['DESCRIPTION'];
        }
        $isInSection = 0;
        $section = 0;
        if ($categoryArray[$arTicket['CATEGORY_ID']] == 'IT') {
            
            $section = 1;
            $isInSection = ( in_array( 16, CUser::GetUserGroup($userId) ) || in_array( 19, CUser::GetUserGroup($userId) ) );
            
        } elseif ($categoryArray[$arTicket['CATEGORY_ID']] == 'AXO') {
            
            $section = 2;
            $isInSection = ( in_array( 17, CUser::GetUserGroup($userId) ) || in_array( 20, CUser::GetUserGroup($userId) ) );
            
        } elseif ($categoryArray[$arTicket['CATEGORY_ID']] == 'HR') {
            
            $section = 3;
            $isInSection = ( in_array( 18, CUser::GetUserGroup($userId) ) || in_array( 21, CUser::GetUserGroup($userId) ) );
        }
        
//         echo "<h1>$section</h1>";
        
//         echo '<pre>';
//         print_r($request);
//         echo '</pre>'; die;
        $arTicket['SECTION_ID'] = $section;
        $arTicket['SOURCE_ID']  = $request['SOURCE_ID'];
        $arTicket['ADDRESS_NAME'] = !empty($request["ADDRESS_NAME"]) ? $request["ADDRESS_NAME"] : '';
        
        if ($section) {
        
            switch ($section) {
        		case 1:
        			$arTicket['IS_CONFIRMED'] = 1;
        			break;
        		case 2:
        			$userId = $USER->GetID();
        			$departmentStructure = CIntranetUtils::GetStructure();
        			$userDepartmentId = array_shift(CIntranetUtils::GetUserDepartments($userId));
        			$userHead = $departmentStructure['DATA'][$userDepartmentId]['UF_HEAD'];
        			$arTicket['IS_CONFIRMED'] = 1;
        			if ($userHead != $userDepartmentId) {
        				$arTicket['IS_CONFIRMED'] = 0;
        				$needToConfirm = 1;
        			}
        			$arTicket['CATEGORY_ID'] = 23;
        			
        			break;
        		case 3:
        			$arTicket['IS_CONFIRMED'] = 1;
        			break;
        	}
        
        }
        
//         echo "<h1>needToConfirm = $needToConfirm</h1>";
        

        if (IsModuleInstalled('intranet')) {
            //group
            if ($arParams['GROUP_ID'] > 0) {
                $arTicket['GROUP_ID'] = $arParams['GROUP_ID'];
            } else {
                if ($request['GROUP_ID'] > 0) {
                    if ($arParams['ROLE'] == 'C') {
                        $group = CSocNetUserToGroup::GetList(
                            array("GROUP_NAME" => "ASC"),
                            array(
                                "USER_ID" => $USER->GetID(),
                                "GROUP_ACTIVE" => "Y",
                                "<=ROLE" => SONET_ROLES_MODERATOR
                            ),
                            false,
                            false,
                            array("ID")
                        );
                        if ($group->Fetch()) {
                            $arTicket['GROUP_ID'] = $request['GROUP_ID'];
                        }
                    }
                    if ($arParams['ROLE'] == 'W') {
                        $arTicket['GROUP_ID'] = $request['GROUP_ID'];
                    }
                }
            }
        }

        $arTicket = array_merge($arTicket, $arUserFields);
        
        if ($arParams['isWorker'] && !in_array($request["CATEGORY_ID"], $arParams['HAVE_CREATE_TO_CAREGORY'])) {
            $arResult["ERRORS"][] = GetMessage('ALTASIB_SUPPORT_ERROR_CATEGORY');
        }

        if (empty($arResult["ERRORS"])) {
            if ($arParams["USE_CAPTCHA"] == "Y" && $arParams["ID"] <= 0) {
                if (!$APPLICATION->CaptchaCheckCode($_REQUEST["captcha_word"], $_REQUEST["captcha_sid"])) {
                    $arResult["ERRORS"] .= GetMessage("I_RECEPTION_FORM_WRONG_CAPTCHA");
                }
            }

            $result = Support\TicketTable::add($arTicket);
            $TICKET_ID = $result->getId();
            if (!$result->isSuccess()) {
            	
                $arResult = $arTicket;
                if ($arParams['ALLOW'] && (int)$request['PARRENT_MESSAGE_ID'] > 0) {
                    $arResult['PARRENT_MESSAGE_ID'] = $request['PARRENT_MESSAGE_ID'];
                }
                $arResult["ERRORS"] = $result->getErrorMessages();
            } else {
            	
//             	$inCrmGroup = ( in_array( 18, CUser::GetUserGroup($USER->GetID()) ) );

            	$group = CGroup::GetListEx(Array(),Array(),0,0,Array('*'));
            	$groupsArray = array();
            	while($record = $group->fetch() ) {
            		$groupsArray[$record['ID']][] = $record['USER_USER_ID'];
            	}
            	
            	$arEvent = getEventDataEx($TICKET_ID);
            	
//             	echo "<h1>userId = $userId</h1>";
//             	echo "<h1>userHead = $userHead</h1>";
//             	die;
            	
            	$notifyResult = [];
            	
            	if ($needToConfirm) {
            		
            		$notifyHeadRes = \CIMNotify::add(array(
            				'FROM_USER_ID' => $userId,
            				'TO_USER_ID' => $userHead,
            				"NOTIFY_TYPE" => IM_NOTIFY_FROM,
            				"NOTIFY_MODULE" => "altasib.support",
            				"NOTIFY_EVENT" => 'support_new',
            				"NOTIFY_ANSWER" => "Y",
            				"NOTIFY_TAG" => "ALTASIB|SUPPORT|TICKET|" . $TICKET_ID,
            				'NOTIFY_MESSAGE' => 'Ожидание подтверждения обращения #'.$TICKET_ID.' ('.$arEvent['URL'].')',
            				'NOTIFY_MESSAGE_OUT' => 'Ожидание подтверждения обращения #'.$TICKET_ID.' ('.$arEvent['URL'].')',
            		));
            		
            	}
            	
            	if ($arEvent['TICKET_RESPONSIBLE_USER_ID'] == -1 && !empty($_GET['section'])) {
            		
            		switch ($_GET['section']) {
            			case 1:
            				foreach ($groupsArray[16] as $userId) {
            					$notifyResult[16][$TICKET_ID][$userId] = \CIMNotify::add(array(
            							'FROM_USER_ID' => $arEvent['TICKET_OWNER_USER_ID'],
            							'TO_USER_ID' => $userId,
            							"NOTIFY_TYPE" => IM_NOTIFY_FROM,
            							"NOTIFY_MODULE" => "altasib.support",
            							"NOTIFY_EVENT" => 'support_new',
            							"NOTIFY_ANSWER" => "Y",
            							"NOTIFY_TAG" => "ALTASIB|SUPPORT|TICKET|" . $TICKET_ID,
            							'NOTIFY_MESSAGE' => Loc::getMessage('ALTASIB_SUPPORT_EVENT_NEW_TICKET', array(
            									'#ID#' => $TICKET_ID,
            									'#TITLE#' => $arEvent['TICKET_TITLE'],
            									'#MESSAGE#' => $arEvent['TICKET_MESSAGE'],
            									'#URL#' => $arEvent['URL']
            							)),
            							'NOTIFY_MESSAGE_OUT' => Loc::getMessage('ALTASIB_SUPPORT_EVENT_NEW_TICKET', array(
            									'#ID#' => $TICKET_ID,
            									'#TITLE#' => $arEvent['TICKET_TITLE'],
            									'#MESSAGE#' => $arEvent['TICKET_MESSAGE'],
            									'#URL#' => $arEvent['URL']
            							)),
            					));
            				}
            				break;
            			case 2:
            				
            				break;
            			case 3:
            				foreach ($groupsArray[18] as $userId) {
            					$notifyResult[18][$TICKET_ID][$userId] = \CIMNotify::add(array(
            							'FROM_USER_ID' => $arEvent['TICKET_OWNER_USER_ID'],
            							'TO_USER_ID' => $userId,
            							"NOTIFY_TYPE" => IM_NOTIFY_FROM,
            							"NOTIFY_MODULE" => "altasib.support",
            							"NOTIFY_EVENT" => 'support_new',
            							"NOTIFY_ANSWER" => "Y",
            							"NOTIFY_TAG" => "ALTASIB|SUPPORT|TICKET|" . $TICKET_ID,
            							'NOTIFY_MESSAGE' => Loc::getMessage('ALTASIB_SUPPORT_EVENT_NEW_TICKET', array(
            									'#ID#' => $TICKET_ID,
            									'#TITLE#' => $arEvent['TICKET_TITLE'],
            									'#MESSAGE#' => $arEvent['TICKET_MESSAGE'],
            									'#URL#' => $arEvent['URL']
            							)),
            							'NOTIFY_MESSAGE_OUT' => Loc::getMessage('ALTASIB_SUPPORT_EVENT_NEW_TICKET', array(
            									'#ID#' => $TICKET_ID,
            									'#TITLE#' => $arEvent['TICKET_TITLE'],
            									'#MESSAGE#' => $arEvent['TICKET_MESSAGE'],
            									'#URL#' => $arEvent['URL']
            							)),
            					));
            				}
            				break;
            		}
            		
            	}
            	
            	
//             	echo '<pre>'; print_r($notifyResult); echo '</pre>';
            	
            	
                //if create task
                if (Main\Loader::includeModule("tasks") && $arParams['USE_TASK']) {
                    $ticketData = Support\TicketTable::getList(array(
                        'filter' => array('ID' => $TICKET_ID),
                        'select' => array('RESPONSIBLE_USER_ID')
                    ))->fetch();
                    if ($ticketData['RESPONSIBLE_USER_ID'] > 0) {
                        $arFields = Array(
                            "TITLE" => GetMessage('ALTASIB_SUPPORT_TASK_PLAN',
                                array('#ID#' => $TICKET_ID, '#NAME#' => $request["TITLE"])),
                            "DESCRIPTION" => $request["MESSAGE"] . GetMessage('ALTASIB_SUPPORT_TASK_PLAN_DETAIL',
                                    array('#URL#' => str_replace("#ID#", $TICKET_ID, $arParams["URL_DETAIL"]))),
                            "CREATED_BY" => $ticketData['RESPONSIBLE_USER_ID'],
                            "RESPONSIBLE_ID" => $ticketData['RESPONSIBLE_USER_ID'],
                            "GROUP_ID" => 12,
                        );

                        $obTask = new CTasks;
                        $taskID = $obTask->Add($arFields);
                        Support\Tools::taskPlanner($taskID, $ticketData['RESPONSIBLE_USER_ID']);
                        Support\TicketTable::update($TICKET_ID, array('TASK_ID' => $taskID));
                    }
                }
                $_SESSION["TICKET_OK"] = true;
                if ($arTicket['GROUP_ID'] == 0) {
                    LocalRedirect(str_replace("#ID#", $TICKET_ID, $arParams["URL_DETAIL"]));
                } else {
                    LocalRedirect(str_replace(array("#ID#", '#TICKET_ID#', '#group_id#'),
                        array($TICKET_ID, $TICKET_ID, $arTicket['GROUP_ID']),
                        COption::GetOptionString('altasib.support', 'path_group_detail')));
                }
            }
        } else {
            $arResult = $arTicket;
        }
    } else {
        if ($request["OPEN"] == "Y") {
            if ($GLOBALS["USER"]->IsAuthorized() && CModule::IncludeModule("pull") && CPullOptions::GetNginxStatus()) {
                $pullParams = array(
                    'TICKET_ID' => $TICKET_ID,
                );
                CPullWatch::AddToStack($arParams["PULL_TAG"],
                    Array(
                        'module_id' => 'altasib.support',
                        'command' => 'open',
                        'params' => $pullParams
                    )
                );
                CPullWatch::AddToStack($arParams["PULL_TAG_SUPPORT"],
                    Array(
                        'module_id' => 'altasib.support',
                        'command' => 'open',
                        'params' => $pullParams
                    )
                );
                CPullWatch::AddToStack($arParams["PULL_TAG_SUPPORT_ADMIN"],
                    Array(
                        'module_id' => 'altasib.support',
                        'command' => 'open',
                        'params' => $pullParams
                    )
                );
            }
            Support\TicketTable::close($TICKET_ID, false, true);
            if (!empty($request["t_submit_go"])) {
                LocalRedirect($arParams["TICKET_LIST_URL"]);
            } else {
                LocalRedirect(str_replace("#ID#", $TICKET_ID, $arParams["URL_DETAIL"]));
            }
        }

        $arTicketMessage = Array(
            "TICKET_ID" => $TICKET_ID,
            "MESSAGE" => $_POST["MESSAGE"],
            'IS_HIDDEN' => 'N',
            "FILES" => $request["FILES"],
            'CLOSE' => $request["CLOSE"],
            'IS_DEFERRED' => $request["IS_DEFERRED"]
        );

        //preg_match_all('/\[IMG(.+)](.+)\[\/IMG]/i',$arTicketMessage['MESSAGE'],$paste_img);
        preg_match_all('/\[img((.+)|)](.+)\[\/img]/i', $arTicketMessage['MESSAGE'], $paste_img);
        if (count($paste_img[3]) > 0) {
            foreach ($paste_img[3] as $k => $img) {
                if (strstr($img, 'data:image')) {
                    $arTicketMessage['MESSAGE'] = str_replace($paste_img[0][$k], '&nbsp;', $arTicketMessage['MESSAGE']);
                    $type = 'png';
                    if (strstr($img, 'data:image/jpg;base64')) {
                        $type = 'jpg';
                    }
                    if (strstr($img, 'data:image/gif;base64')) {
                        $type = 'gif';
                    }

                    $pImg = str_replace(' ', '+', str_replace('data:image/' . $type . ';base64,', '', $img));
                    $arTicketMessage['FILES'][] = CFile::SaveFile(array(
                        'name' => uniqid() . '.' . $type,
                        'type' => 'image/jpeg',
                        'content' => base64_decode($pImg),
                        'MODULE_ID' => 'altasib.support'
                    ), 'altasib.support/base64', true);
                }
            }
        }

        $close_ex = false;
        if ($arParams['IS_SUPPORT_TEAM']) {
            $arTicketMessage['NOT_CHANGE'] = $request['NOT_CHANGE'] == 'Y' ? 'Y' : 'N';
            $arTicketMessage['IS_HIDDEN'] = $request['IS_HIDDEN'] == 'Y' ? 'Y' : 'N';

            if ($request['CLOSE_EX'] == 'Y') {
                $arTicketMessage['CLOSE_EX'] = $request['CLOSE_EX'];
                $close_ex = true;
            }
        }

        if (strlen($arTicketMessage['MESSAGE']) == 0 && $request["CLOSE"] == 'Y') {
            $close_ex = true;
        }

        $result = Support\TicketMessageTable::add($arTicketMessage);
        if (!$close_ex) {
            $TICKET_MESSAGE_ID = $result->getId();
        }

        if (!$result->isSuccess()) {
            $arResult["ERRORS"] = $result->getErrorMessages();
            if ($ajax) {
                echo CUtil::PhpToJSObject(array('status' => false, 'error' => implode('<br />', $arResult["ERRORS"])));
                die();
            }
        } else {
            if (!$close_ex) {
                $pull = true;
                if (!isset($request['AJAX_CALL']) && $GLOBALS["USER"]->IsAuthorized() && CModule::IncludeModule("pull") && CPullOptions::GetNginxStatus()) {
                    $pullParams = array(
                        'ID' => $TICKET_MESSAGE_ID,
                        'TICKET_ID' => $TICKET_ID,
                        'IS_HIDDEN' => $arTicketMessage['IS_HIDDEN']
                    );
                    if (!function_exists('getMessageSupport')) {
                        include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/components/altasib/support.ticket.detail/templates/.default/message_template.php');
                    }

                    $dataTicket = Support\TicketTable::getRow(array(
                        'filter' => array('ID' => $TICKET_ID),
                        'select' => array('*')
                    ));
                    $dataTicketMessage = Support\TicketMessageTable::getRow(array(
                        'filter' => array('ID' => $TICKET_MESSAGE_ID),
                        'select' => array(
                            '*',
                            'CREATED_USER_NAME' => 'CREATED_USER.NAME',
                            'CREATED_USER_LAST_NAME' => 'CREATED_USER.LAST_NAME',
                            'CREATED_USER_SHORT_NAME' => 'CREATED_USER.SHORT_NAME'
                        )
                    ));

                    if ($arTicketMessage['IS_HIDDEN'] == 'N') {
                        $dataTicketMessage['pull_type'] = Support\Tools::$PULL_TYPE_CUSTOMER;
                        $pullParams['MESSAGE'] = getMessageSupport($dataTicketMessage, $arParams, $dataTicket, '');
                        CPullWatch::AddToStack($arParams["PULL_TAG"],
                            Array(
                                'module_id' => 'altasib.support',
                                'command' => 'message',
                                'params' => $pullParams
                            )
                        );
                    }

                    $dataTicketMessage['pull_type'] = Support\Tools::$PULL_TYPE_SUPPORT_TEAM;
                    $pullParams['MESSAGE'] = getMessageSupport($dataTicketMessage, $arParams, $dataTicket, '');
                    CPullWatch::AddToStack($arParams["PULL_TAG_SUPPORT"],
                        Array(
                            'module_id' => 'altasib.support',
                            'command' => 'message',
                            'params' => $pullParams
                        )
                    );

                    $dataTicketMessage['pull_type'] = Support\Tools::$PULL_TYPE_SUPPORT_TEAM_ADMIN;
                    $pullParams['MESSAGE'] = getMessageSupport($dataTicketMessage, $arParams, $dataTicket, '');
                    CPullWatch::AddToStack($arParams["PULL_TAG_SUPPORT_ADMIN"],
                        Array(
                            'module_id' => 'altasib.support',
                            'command' => 'message',
                            'params' => $pullParams
                        )
                    );
                } else {
                    $pull = false;
                }

                Support\Tools::taskPlannerProcess($TICKET_ID, Support\Tools::IsSupportTeam($USER->GetID()));
            }

            if (($request["CLOSE"] == "Y" || $close_ex) && $GLOBALS["USER"]->IsAuthorized() && CModule::IncludeModule("pull") && CPullOptions::GetNginxStatus()) {
                $pullParams = array(
                    'TICKET_ID' => $TICKET_ID,
                );
                CPullWatch::AddToStack($arParams["PULL_TAG"],
                    Array(
                        'module_id' => 'altasib.support',
                        'command' => 'close',
                        'params' => $pullParams
                    )
                );
                CPullWatch::AddToStack($arParams["PULL_TAG_SUPPORT"],
                    Array(
                        'module_id' => 'altasib.support',
                        'command' => 'close',
                        'params' => $pullParams
                    )
                );

                CPullWatch::AddToStack($arParams["PULL_TAG_SUPPORT_ADMIN"],
                    Array(
                        'module_id' => 'altasib.support',
                        'command' => 'close',
                        'params' => $pullParams
                    )
                );

            }
            if ($ajax) {
                $obRes = array('status' => true, 'error' => '', 'messageId' => $TICKET_MESSAGE_ID);
                if (!empty($request["t_submit_go"])) {
                    $obRes['redirect'] = true;
                    $obRes['redirect_url'] = $arParams["TICKET_LIST_URL"];
                } elseif ($request["CLOSE"] == "Y" || $close_ex || !$pull) {
                    $obRes['redirect'] = true;
                    $obRes['redirect_url'] = str_replace("#ID#", $TICKET_ID, $arParams["URL_DETAIL"]);
                    if (!$pull) {
                        $_SESSION["TICKET_MESSAGE_OK"] = true;
                    }
                }

                echo CUtil::PhpToJSObject($obRes);
                die();
            } else {
                $_SESSION["TICKET_MESSAGE_OK"] = true;
                if (!empty($request["t_submit_go"])) {
                    LocalRedirect($arParams["TICKET_LIST_URL"]);
                } else {
                    LocalRedirect(str_replace("#ID#", $TICKET_ID, $arParams["URL_DETAIL"]));
                }
            }
        }
    }
}

if ($arParams["ID"] > 0) {
    $arResult["IS_CLOSE"] = Support\TicketTable::isClose($arParams['ID']);
}

// prepare captcha
if ($arParams["USE_CAPTCHA"] == "Y" && $arParams["ID"] == 0) {
    $arResult["CAPTCHA_CODE"] = htmlspecialchars($APPLICATION->CaptchaGetCode());
}
if ($arParams["ID"] == 0) {
	$arResult["CATEGORY"] = Array();
	$arResult["CATEGORY_SECTIONED"] = Array();
	
    $obCategory = Support\CategoryTable::getList();
    while ($arCategory = $obCategory->fetch()) {
        if ($arParams['isWorker'] && !in_array($arCategory['ID'], $arParams['HAVE_CREATE_TO_CAREGORY'])) {
            continue;
        }

        $arResult["CATEGORY"][$arCategory["ID"]] = $arCategory;
        $arResult["CATEGORY_SECTIONED"][$arCategory['DESCRIPTION']][$arCategory["ID"]] = $arCategory['NAME'];
    }
    
    asort($arResult["CATEGORY_SECTIONED"]['IT']);
    asort($arResult["CATEGORY_SECTIONED"]['HR']);
    
//     $arResult["CATEGORY_SECTIONED"]['HR']['first']  = [];
//     $arResult["CATEGORY_SECTIONED"]['HR']['second'] = [];
//     $arResult["CATEGORY_SECTIONED"]['HR']['thirth'] = [];
    
//     foreach ($arResult["CATEGORY_SECTIONED"]['HR'] as $value) {
//     	$subarray = array_map('intval', explode('.', $value));
    
//     	if (is_numeric($subarray[0])) {
//     		if (!empty($subarray[1]) && is_numeric($subarray[1]) && $subarray[1] > 0) {
//     			if (!empty($subarray[2]) && is_numeric($subarray[2]) && $subarray[2] > 0) {
//     				$arResult["CATEGORY_SECTIONED"]['HR']['thirth'][$subarray[0]][$subarray[1]][$subarray[2]] = trim(preg_replace('/[0-9]+./', '', $value));
//     			} else {
//     				$arResult["CATEGORY_SECTIONED"]['HR']['second'][$subarray[0]][$subarray[1]] = trim(preg_replace('/[0-9]+./', '', $value));
//     			}
//     		} else {
//     			$arResult["CATEGORY_SECTIONED"]['HR']['first'][$subarray[0]] = trim(preg_replace('/[0-9]+./', '', $value));
//     		}
    
//     	}
//     }
    
//     $arResult["CATEGORY_SECTIONED"]['IT']['first']  = [];
//     $arResult["CATEGORY_SECTIONED"]['IT']['second'] = [];
//     $arResult["CATEGORY_SECTIONED"]['IT']['thirth'] = [];
    
//     foreach ($arResult["CATEGORY_SECTIONED"]['IT'] as $value) {
//     	$subarray = array_map('intval', explode('.', $value));
    
//     	if (is_numeric($subarray[0])) {
//     		if (!empty($subarray[1]) && is_numeric($subarray[1]) && $subarray[1] > 0) {
//     			if (!empty($subarray[2]) && is_numeric($subarray[2]) && $subarray[2] > 0) {
//     				$arResult["CATEGORY_SECTIONED"]['IT']['thirth'][$subarray[0]][$subarray[1]][$subarray[2]] = trim(preg_replace('/[0-9]+./', '', $value));
//     			} else {
//     				$arResult["CATEGORY_SECTIONED"]['IT']['second'][$subarray[0]][$subarray[1]] = trim(preg_replace('/[0-9]+./', '', $value));
//     			}
//     		} else {
//     			$arResult["CATEGORY_SECTIONED"]['IT']['first'][$subarray[0]] = trim(preg_replace('/[0-9]+./', '', $value));
//     		}
    
//     	}
//     }
    
//     echo '<pre>';
//     print_r($arResult["CATEGORY_SECTIONED"]);
//     echo '</pre>';
    
    
    $arResult["PRIORITY"] = Support\Priority::get();
    $arResult['SLA'] = Support\SlaTable::getUserSla($USER->GetID());

    if ($arParams['ALLOW'] && (int)$request['PARRENT_MESSAGE'] > 0 && empty($arResult['MESSAGE'])) {
        $parentMessage = Support\TicketMessageTable::getRow(array(
            'filter' => array('ID' => $request['PARRENT_MESSAGE']),
            'select' => array('*', 'TICKET_CATEGORY_ID' => 'TICKET.CATEGORY_ID')
        ));
        $arResult['PARRENT_MESSAGE_ID'] = $request['PARRENT_MESSAGE'];
        $arResult['MESSAGE'] = $parentMessage['MESSAGE'];
        $arResult['CATEGORY_ID'] = $parentMessage['TICKET_CATEGORY_ID'];

    }
    $arParams['CUSTOMER_LIST'] = array();
    if ($arParams['ALLOW']) {
        $arParams['CUSTOMER_LIST'] = Support\Tools::getCustomerList();
    } else {
        $arParams['CUSTOMER_LIST'] = array();
        $clientData = Support\ClientTable::getList(array('filter' => array('RESPONSIBLE_USER_ID' => $USER->GetID())));
        while ($client = $clientData->Fetch()) {
            $userData = \Bitrix\Main\UserTable::getRow(array('filter' => array('ID' => $client['USER_ID'])));
            $arParams['CUSTOMER_LIST'][$userData["ID"]] = "[" . $userData["LOGIN"] . "] " . $userData["NAME"] . " " . $userData["LAST_NAME"];
        }
    }
    
}

if ($arParams['IS_SUPPORT_TEAM']) {
    $arParams['QuickResponse'] = Support\QuickResponseTable::getList()->fetchAll();
}

$arParams['SHOW_GROUP_SELECTOR'] = false;
if (IsModuleInstalled('intranet')) {
    $arParams['SHOW_GROUP_SELECTOR'] = true;
}

// $arResult['SOURCE_ARRAY'] = array(
//     1 => 'Портал',
//     2 => 'Сайт',
//     3 => 'Магазин',
// );

$arSelect = Array("ID","NAME");
$arFilter = Array("IBLOCK_ID" => 12);
$db = CIBlockElement::GetList(Array(), $arFilter, false, Array(), $arSelect);
while ($record = $db->fetch())
{
	if ($record['NAME'] == 'Портал') {
		$arResult['SELECTED_SOURCE'] = $record['ID'];
	}
	
	$arResult['SOURCE_ARRAY'][$record['ID']] = $record['NAME'];
}


$this->IncludeComponentTemplate();

CJSCore::Init(array('fx'));
?>