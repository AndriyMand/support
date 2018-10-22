<?
#################################################
#   Company developer: ALTASIB                  #
#   Developer: Evgeniy Pedan, ESVSerge          #
#   Site: http://www.altasib.ru                 #
#   E-mail: dev@altasib.ru                      #
#   Copyright (c) 2006-2016 ALTASIB             #
#################################################
?>

<link href="https://www.jqueryscript.net/css/jquerysctipttop.css" rel="stylesheet" type="text/css">



<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
use ALTASIB\Support;

// echo '<h1>' . SITE_TEMPLATE_PATH . '</h1>';
$this->addExternalCss(SITE_TEMPLATE_PATH."/css/rating/starability-minified/starability-all.min.css");
$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/choosen.js");

?>
<?if($arParams["ID"]>0):

if (!empty($_GET['take-over']) && $_GET['take-over'] == 1 && $arResult['TICKET_INFO']['RESPONSIBLE_USER_ID'] == -1) {
	$ID = $USER->GetID();
	$result = Support\TicketTable::update($arParams["ID"],array('RESPONSIBLE_USER_ID'=>$ID,'MODIFIED_USER_ID'=>$ID));
	if($result->isSuccess())
	{
		$arResult['EXTENDED_MENU'] = 1;
		Support\Event::changeResponsible($arParams["ID"]);
	}
}

$currentUserId = $USER->GetID();

if (!empty($_POST['take-over-from']) && $_POST['take-over-from'] == 1 && $arResult['TICKET_INFO']['RESPONSIBLE_USER_ID'] == $currentUserId) {
	$result = Support\TicketTable::update($arParams["ID"],array('RESPONSIBLE_USER_ID' => -1,'MODIFIED_USER_ID'=>$currentUserId));
	
	if($result->isSuccess())
	{
		Support\Event::changeResponsible($arParams["ID"]);
		$arResult['TICKET_INFO']['RESPONSIBLE_USER_ID'] = -1;
		$arResult['EXTENDED_MENU'] = 0;
		
		$groupRelation = array(
			1 => 16,
			2 => 17,
			3 => 18,
		);
		
		$group = CGroup::GetListEx(Array(),Array(),0,0,Array('*'));
		$groupsArray = array();
		while($record = $group->fetch() ) {
			$groupsArray[$record['ID']][] = $record['USER_USER_ID'];
		}
		
		
		foreach ($groupsArray[$groupRelation[$arResult['TIKET']['SECTION_ID']]] as $userId) {
			\CIMNotify::add(array(
					'FROM_USER_ID' => $currentUserId,
					'TO_USER_ID' => $userId,
					"NOTIFY_TYPE" => IM_NOTIFY_FROM,
					"NOTIFY_MODULE" => "altasib.support",
					"NOTIFY_EVENT" => 'support_new',
					"NOTIFY_ANSWER" => "Y",
					"NOTIFY_TAG" => "ALTASIB|SUPPORT|TICKET|",
					'NOTIFY_MESSAGE' => "Обращение <a href='/support/ticket/{$arResult['TIKET']['ID']}/'>{$arResult['TIKET']['TITLE']}</a> снято с обработки",
                    'NOTIFY_MESSAGE' => "Обращение <a href='/support/ticket/{$arResult['TIKET']['ID']}/'>{$arResult['TIKET']['TITLE']}</a> снято с обработки",
			));
		}
	}
}

?>
<?

$arParams['USER_IS_OWNER'] = $arResult['USER_IS_OWNER'];

if($arParams['HAVE_CHANGE_CATEGORY'] || $arParams['HAVE_CHANGE_PRIORITY'] || $arParams['HAVE_CHANGE_STATUS'] || ($arParams['HAVE_CHANGE_RESPONSIBLE'] || $arResult["TICKET_INFO"]["RESPONSIBLE_USER_ID"]==$USER->GetID()) || $arParams['IS_SUPPORT_TEAM'])
    include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/script.php");
    
CJSCore::Init(Array("viewer"));    
include_once('message_template.php');
?>
<script>
	BX.message({
		SUPPORT_LOAD_MESSAGE_ALL : '<?php echo CUtil::JSEscape(GetMessage("SUPPORT_LOAD_MESSAGE_ALL")); ?>',
	});

	BX.ready(function() {    

		 $('.rate-star').on('click',function(e){

			 var redirect = pathname + '?change-rate=' + $(this).data('rate');
			 window.location.replace(redirect);

		});	

		$('#create_task_btn').on('click',function(e){

			 $('#create_task').show(500);

		});

		 
	});
</script>
<?if($arParams['SHOW_GROUP_SELECTOR']):
    $APPLICATION->IncludeComponent('bitrix:socialnetwork.group.selector', 'group', array(
        //'SELECTED' => $arResult["TICKET_INFO"]['GROUP_ID'],
        'BIND_ELEMENT' => 'support_ticket_group',
        'ON_SELECT' => 'supportChangeGroup',
        'SUPPORT_ROLE' => $arParams['ROLE'],
        ), 
        $component, 
        array('HIDE_ICONS' => 'Y')
    );
endif;?>
	<div class="head_wrap_t">
        <div class="ticket_title">#<?=$arResult["TICKET_INFO"]['ID']?> <?=$arResult["TICKET_INFO"]["TITLE"];?><?if($arResult["TICKET_INFO"]["IS_DEFERRED"]=='Y'):?> <span id="deffered">(<?=GetMessage("ALTASIB_SUPPORT_TICKET_DETAIL_IS_DEFERRED");?>)</span><?endif;?></div>
        <?/*<table border="0" cellpadding="0" cellspacing="2" width="100%" class="ticketn">
                <tr>
                        <th colspan="4" class="title_ticket_main">
                                <div class="title_block">
                                #<?=$arResult["TICKET_INFO"]['ID']?> <?=$arResult["TICKET_INFO"]["TITLE"];?><?if($arResult["TICKET_INFO"]["IS_DEFERRED"]=='Y'):?> <span id="deffered">(<?=GetMessage("ALTASIB_SUPPORT_TICKET_DETAIL_IS_DEFERRED");?>)</span><?endif;?>
                                <span style="float: right;">
                                <span id="support_ticket_group"><?=$arResult["TICKET_INFO"]['GROUP_NAME'];?></span>
                                <?if(IsModuleInstalled('socialnetwork')):?>
                                <?if($arParams['ALLOW'] || $USER->GetID()==$arResult["TICKET_INFO"]['OWNER_USER_ID']):?><a href="javascript:void(0)" onclick="groupsPopup.show()" id="support_ticket_group_selector"><?if($arResult["TICKET_INFO"]['GROUP_ID']>0):?><?=GetMessage('ALTASIB_SUPPORT_EDIT_FORM_PROJECT_CHANGE')?><?else:?><?=GetMessage('ALTASIB_SUPPORT_EDIT_FORM_PROJECT_SELECT')?><?endif;?></a><?endif;?>
                                <?endif;?>

                                </span>
                                </div>
                                <div class="line_dotted_main">&nbsp;</div>
                        </td>
                </tr>
        </table>*/?>			
			<table border="0" cellpadding="0" cellspacing="2" width="100%" class="ticketn ticketn_mess">
                <tr>
                        <td class="dop_title_td">
							<div class="dop_title_block"><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_TT");?></div>
							<!-- <div style="float: right;">
								<span id="support_ticket_group"><?=$arResult["TICKET_INFO"]['GROUP_NAME'];?></span>
								<?if(IsModuleInstalled('socialnetwork')):?>
								<?if(IsModuleInstalled('intranet') && ($arParams['ALLOW'] || $USER->GetID()==$arResult["TICKET_INFO"]['OWNER_USER_ID'])):?><a href="javascript:void(0)" onclick="groupsPopup.show()" id="support_ticket_group_selector"><?if($arResult["TICKET_INFO"]['GROUP_ID']>0):?><?=GetMessage('ALTASIB_SUPPORT_EDIT_FORM_PROJECT_CHANGE')?><?else:?><?=GetMessage('ALTASIB_SUPPORT_EDIT_FORM_PROJECT_SELECT')?><?endif;?></a><?endif;?>
								<?endif;?>
							</div> -->
						</td>
                        <td class="altasib_text_mess_td" id="altasib_text_mess_td_0">
							<div class="altasib_text_div" id="altasib_text_div_0"><div class="altasib_text_div_inner"><?=$arResult["TICKET_INFO"]["MESSAGE"];?>
								<div class="altasibTicketPostMoreButton" id="altasibTicketPostMoreButton_0" onclick="showAltasibTicketPost('0', this)">
									<div class="altasibTicketPostMoreBut"></div>
								</div>							
							</div></div>
						</td>
                </tr>
			<?if(count($arResult["TICKET_INFO"]["FILES"])>0 || count($arResult["TICKET_INFO"]["FILES_IMAGE"])>0):?>
				
                
				<tr>
						<td class="text_add_files_td">
						<br /><div class="dop_title_block_2"><?=GetMessage("ALTASIB_SUPPORT_TICKET_DETAIL_FILES");?>:</div>
							<?foreach($arResult["TICKET_INFO"]["FILES_IMAGE"] as $arFile):?>
							<?$minImage = CFile::ResizeImageGet($arFile, Array("width" => 75, "height" => 50),BX_RESIZE_IMAGE_EXACT);?>
							<?=CFile::ShowImage($minImage['src'], 9999, 50, 
								'border=0 
								data-bx-viewer="image" 
								data-bx-title="'.$arFile['ORIGINAL_NAME'].'" 
								data-bx-src="'.$arFile['SRC'].'" 
								data-bx-download="'.$arFile['SRC'].'" 
								data-bx-width="'.$arFile['WIDTH'].'" 
								data-bx-height="'.$arFile['HEIGHT'].'"', 
							"", false);?>							
							<?endforeach;?>						
                            <br />
							<?foreach($arResult["TICKET_INFO"]["FILES"] as $arFile):?>
							<a target="_blank" href="<?=$arFile["URL"]?>"><?=$arFile["ORIGINAL_NAME"]?></a> <small>(<?=$arFile["FORMAT_FILE_SIZE"]?>)</small><br />
							<?endforeach;?>
						</td>
				</tr>
				
			<?endif;?>
			</table>
				<?/*if(count($arResult["TICKET_INFO"]["FILES"])>0 || count($arResult["TICKET_INFO"]["FILES_IMAGE"])>0):?>
				<table border="0" cellpadding="0" cellspacing="2" width="100%" class="ticketn"> 				
                <tr>
                        <td align="center" class="dop_title_td"><div class="dop_title_block"><?=GetMessage("ALTASIB_SUPPORT_TICKET_DETAIL_FILES");?></div></td>
                </tr>
				<tr class="title_mess">
						<td>
							<?foreach($arResult["TICKET_INFO"]["FILES_IMAGE"] as $arFile):?>
							<?=CFile::ShowImage($arFile['SRC'], 9999, 50, "border=0", "", true);?>
							<?endforeach;?>
                            <br />
							<?foreach($arResult["TICKET_INFO"]["FILES"] as $arFile):?>
							<a target="_blank" href="<?=$arFile["URL"]?>"><?=$arFile["ORIGINAL_NAME"]?></a> <?=$arFile["FORMAT_FILE_SIZE"]?>
							<?endforeach;?>
						</td>
				</tr>
				</table><br />
				<?endif;*/?>

                <?if($arParams["USER_FIELDS_SHOW"]):?>
				<table border="0" cellpadding="0" cellspacing="2" width="100%" class="ticketn">  
				<tr>
                        <td align="center" class="dop_title_td"><div class="dop_title_block"><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_MORE_INFO");?></div></td>
                </tr>
                            <?foreach($arParams["USER_FIELDS"] as $FIELD_NAME=>$arUserField):
                            if($arUserField["USER_TYPE_ID"] == "video" || $arUserField["USER_TYPE_ID"] == "file" || $arUserField["USER_TYPE_ID"] == "iblock_section" || $arUserField["USER_TYPE_ID"] == "iblock_element")
                                continue;
                            ?>
                <tr>
                    <td>
                            <?if ((is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0) || (!is_array($arUserField["VALUE"]) && StrLen($arUserField["VALUE"]) > 0)):?>
                                    <b><?=$arUserField["EDIT_FORM_LABEL"]?>:</b>
                                        <?$APPLICATION->IncludeComponent(
                                                "bitrix:system.field.view",
                                                $arUserField["USER_TYPE"]["USER_TYPE_ID"],
                                                array("arUserField" => $arUserField), null, array("HIDE_ICONS"=>"Y")
                                            );
                                        ?>
                            <?endif;?>
                    </td>
                </tr>       

                            <?endforeach;?>
                </table><br />
				<?endif;?>
				
				<? if ($arResult['TIKET']['TASK_ID'] && $arResult['TIKET']['GROUP_ID']) { ?>
        				<p>
        				Создана задача <a href="/workgroups/group/<?=$arResult['TIKET']['GROUP_ID']?>/tasks/task/view/<?=$arResult['TIKET']['TASK_ID']?>/">#<?=$arResult['TIKET']['TASK_ID']?></a>
                      	</p>
    				
				<? } ?>
				
				<? $disabledBtns = ($arResult['TIKET']['RESPONSIBLE_USER_ID'] <= 0) ? 'disabled' : ''; ?>
				
				<form class="task_btns" action="" method="post">
    				<div>
    				<? if ($arResult['EXTENDED_MENU'] && !$arResult['IS_CLOSED_STATUS']) { ?>
        				<? if ($arResult['HAS_GROUP']) { ?>
        					<a href="javascript:void(0)" id="create_task_btn" class="btn btn-primary" role="button" <?=$disabledBtns?>><i class="g-icon transmit-icon"></i> Переадресация</a>
    					<? } ?>	
					 	<button class="btn btn-warning" name="take-over-from" type="submit" value="1" <?=$disabledBtns?>><i class="glyphicon glyphicon-minus"></i> Снять с обработки</button>
    				<? } ?>
    				
    				<? if ($arResult['EXTENDED_MENU']) { ?>
    					<a href="javascript:void(0)" id="add-member-choice" class="btn btn-info" role="button" <?=$disabledBtns?>><i class="glyphicon glyphicon-plus"></i> Добавить наблюдателя</a>	
    				<? } ?>
    				
    				<? if (($arResult['EXTENDED_MENU'] || $arResult['USER_IS_OWNER']) && !$arResult['IS_CLOSED_STATUS']) { ?>
    					<button class="btn btn-success" name="close-ticket" type="submit" value="1" <?=$disabledBtns?>><i class="glyphicon glyphicon-ok"></i> Закрыть обращение</button>
    				<? } ?>
    				</div>
    				
    				<? if ($arResult['CAN_CONFIRM']) { ?>
    				<div>
    					<a href="/support/ticket/<?=$arParams["ID"];?>/?confirm=1" class="btn btn-success" role="button"><i class="g-icon take-icon"></i> Взять в работу</a>	
    					<a href="/support/ticket/<?=$arParams["ID"];?>/?confirm=0" class="btn btn-danger" role="button"><i class="glyphicon glyphicon-remove"></i> Отклонить</a>	
    				</div>
    				<? } ?>
				
				</form>
				
				<? if ($arResult['HAS_GROUP']) { ?>
    				<form action="" method="post" style="display:none;" id="create_task" name="create_task">
        				<fieldset>
                            <legend>Создание задачи:</legend>
                            Кому:<br>
                            
                            <select name="task_responsible" class="chosen">
								<?php 
                            	foreach ($arResult['ALL_USERS'] as $userId => $userName) {
                            	    echo "<option value='$userId'>$userName</option>";
                            	}
                            	?>
							</select>
                            
                            <script type="text/javascript">
                           		$(".chosen").chosen();
                            </script>
                            
                            <br>
                            Описание:<br>
                            <textarea name="task_description" rows="4" cols="50"></textarea>
        					<br>
                            <input type="submit" value="Создать">
                      	</fieldset>
    				
    				</form>
				<? } ?>
				
			<!--	<div class="dop_title_block_div <?if($arResult['MESSAGE_CNT']>5):?>dashed-bottom<?endif?>">
								
				</div> -->
				
				<?php 
				
				if ( $arResult['IS_CLOSED_STATUS'] ) {
					
					$disabledStars = 'style="pointer-events: none;"';
					$preMarkTitle = 'Оценка инициатора';
					$stabilityClass = 'basic';
					if ( $arResult['USER_IS_OWNER'] ) {
						$disabledStars = '';
						$preMarkTitle = 'Ваша оценка';
						$stabilityClass = 'coinFlip';
					}
					?>
					
				 	<div class="row">
					    <div class="col-sm-3 your_mark_title"><?=$preMarkTitle;?></div>
					    <div class="col-sm-4 your_mark">
	
							 <form <?=$disabledStars;?>>
							    <fieldset class="starability-<?=$stabilityClass;?>">
							      	<input type="radio" id="no-rate" class="input-no-rate" name="rating" value="0" checked aria-label="No rating." />
								
							      	<input type="radio" id="rate1" name="rating" value="1" <?=$arResult['rate_1'];?> />
							      	<label for="rate1" class="rate-star" data-rate="1" title="Так себе">Так себе</label>
								
							      	<input type="radio" id="rate2" name="rating" value="2" <?=$arResult['rate_2'];?> />
							      	<label for="rate2" class="rate-star" data-rate="2" title="Нормальный">Нормальный</label>
								
							      	<input type="radio" id="rate3" name="rating" value="3" <?=$arResult['rate_3'];?> />
							      	<label for="rate3" class="rate-star" data-rate="3" title="Хороший">Хороший</label>
								
						     		<span class="starability-focus-ring"></span>
							    </fieldset>
						 	</form>
				 	
	
						</div>
				  	</div>
			  	
			  	<?php } ?>
			</div>	
			<div class="discus_block">
				<ul class="nav nav-tabs">
				    <li class="active"><a href="#home"><i class="g-icon disc-icon"></i> <?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_DISCUSSION");?></a></li>
				    <li><a href="#menu1"><i class="g-icon his-icon"></i> История<span class="att-items">99</span></a></li>
			 	</ul>
				
			  	<div class="tab-content">
				    <div id="home" class="tab-pane fade in active">
					      <?php 
						  	if (!empty($arResult["NOT_LOGGED_FAVORITE_MESSAGES"]) || !empty($arResult["NOT_LOGGED_MESSAGES"])) {
						  		?>
						  		<table border="0" cellpadding="0" cellspacing="2" width="100%" class="ticketn">                                
					                <tr id="tr-support-mess-list"<?/*if($arResult['MESSAGE_CNT']==0):?> style="display:none"<?endif;*/?>>
					                        <td class="messege_list_tic" id="support-message-list">
					                            <div id="support-messages-favorite">
					                            <?foreach($arResult["NOT_LOGGED_FAVORITE_MESSAGES"] as $arMessage):?>								
					                                <?=getMessageSupport($arMessage,$arParams,$arResult['TICKET_INFO'], $lastDate)?>
					                            <?endforeach;?>
					                            </div>
					                            <div id="support-messages">
												<?$lastDate = ""?>
					                            <?foreach($arResult["NOT_LOGGED_MESSAGES"] as $arMessage):?>								
					                                <?=getMessageSupport($arMessage,$arParams,$arResult['TICKET_INFO'], $lastDate)?>
													<?$lastDate = $arMessage['DATE_CREATE']?>
					                            <?endforeach;?>
					                            </div>
				                            </td>
					                 </tr>
					            </table>
						  		<?php
						  	}
						  	?>
			    	</div>
				    <div id="menu1" class="tab-pane fade">
					      <?php 
						  	if (!empty($arResult["LOGGED_FAVORITE_MESSAGES"]) || !empty($arResult["LOGGED_MESSAGES"])) {
						  		?>
						  		<table border="0" cellpadding="0" cellspacing="2" width="100%" class="ticketn">                                
					                <tr id="tr-support-mess-list"<?/*if($arResult['MESSAGE_CNT']==0):?> style="display:none"<?endif;*/?>>
					                        <td class="messege_list_tic" id="support-message-list">
					                            <div id="support-messages-favorite">
					                            <?foreach($arResult["LOGGED_FAVORITE_MESSAGES"] as $arMessage):?>								
					                                <?=getMessageSupport($arMessage,$arParams,$arResult['TICKET_INFO'], $lastDate)?>
					                            <?endforeach;?>
					                            </div>
					                            <div id="support-messages">
												<?$lastDate = ""?>
					                            <?foreach($arResult["LOGGED_MESSAGES"] as $arMessage):?>								
					                                <?=getMessageSupport($arMessage,$arParams,$arResult['TICKET_INFO'], $lastDate)?>
													<?$lastDate = $arMessage['DATE_CREATE']?>
					                            <?endforeach;?>
					                            </div>
				                            </td>
					                 </tr>
					            </table>
						  		<?php
						  	}
						  	?>
				    </div>
			  	</div>
			  	
			  	
				
				
            <?if($arParams['Right']->isSupportTeam()):?>
            <br />
            <div style="display: none; font-weight: bold;" id="ticketOnlineBlock"><?=GetMessage('ALTASIB_SUPPORT_ONLINE')?>: <span id="ticketOnline"></span></div>
            <?endif;?>
<?endif;?>
<script src="https://code.jquery.com/jquery-1.12.4.min.js"></script>
  