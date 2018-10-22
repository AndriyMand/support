<?
#################################################
#   Company developer: ALTASIB                  #
#   Developer: Evgeniy Pedan                    #
#   Site: http://www.altasib.ru                 #
#   E-mail: dev@altasib.ru                      #
#   Copyright (c) 2006-2010 ALTASIB             #
#################################################
$ITSectionId  = 1;
$AXOSectionId = 2;
$HRSectionId  = 3;
$ahoCategoryId = 23;

use \Bitrix\Main\Application;

// $tableRes = \Bitrix\Main\Application::getConnection();
// $tableRes = $tableRes->queryExecute("ALTER TABLE altasib_support_ticket ADD IS_CONFIRMED int;");
// echo '<pre>'; print_r($tableRes); echo '</pre>';
?>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  
	
  
  	<style>
		.dropdown-submenu {
		    position: relative;
		}
		
		.dropdown-submenu .dropdown-menu {
		    top: 0;
		    left: 100%;
		    margin-top: -1px;
		}
	</style>

<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>


<?if($arParams['ID']===0):?>
	<script>
		BX.ready(function () {
			AltasibSupport.PopupWindow.Init();
		});  
		BX.message({
			
			});
		AltasibSupport.textSave = "<?=GetMessage('ALTASIB_SUPPORT_EDIT_FORM_SAVE')?>";
		AltasibSupport.textCancel = "<?=GetMessage('ALTASIB_SUPPORT_EDIT_FORM_CANCEL')?>";	
	</script>
<?endif;?>

<?if($arParams["HAVE_ANSWER"] || ($arParams['ID']==0 && $arParams["HAVE_CREATE"])):?>
<br style="clear:both;"/>
	<?if (!empty($arResult["ERRORS"])):?>
			<?=ShowError(implode("<br />", $arResult["ERRORS"]));?>
	<br style="clear:both;"/>
	<?endif?>
	<?CJSCore::Init(array('add_js_css'));?>
	<?CJSCore::Init(array('jq_chosen'));?>
<?if($arParams['IS_SUPPORT_TEAM']):?>

	<div id="support-change-qa" style="display: none;"></div>
	<div class="choose_p support-change-qa-popup" id="support-change-qa-popup" style="display: none;">                
		<div class="choose_popup popup_menuItem">
			<select name="QuickResponse" id="quickresponsesel" class="qr">
				<option value="0"></option>
				<?foreach($arParams['QuickResponse'] as $k=>$v):?>
				<option value="<?=$v['ID']?>"><?=$v['NAME']?></option>
				<?endforeach;?>
			</select>
		</div>                
	</div>
	<?foreach($arParams['QuickResponse'] as $k=>$v):?>
	<div style="display: none;" id="QuickResponse-<?=$v['ID']?>"><?=$v['DESCRIPTION']?></div>
	<?endforeach;?>
	<?if($arParams["ID"] > 0):?>
	<script>
	BX.ready(function () {
		BX.addCustomEvent(window, "OnEditorInitedBefore", function(e){
			
			e.AddButton({
				id : 'quickAnswer',
				name : '<?=GetMessage('ALTASIB_SUPPORT_EDIT_QUICK_ANSWER_TITLE')?>',
				iconClassName : 'bxhtmled-button-quick_answer',
				handler : function () {$("#quickresponsesel").chosen();AltasibSupport.PopupMenu.Show(BX('support-change-qa'));},
				src : '/bitrix/components/altasib/support/images/quick_answer.gif'
			});
		});
		
		$('#quickresponsesel').on('change', function(event, params) {
			window["BXHtmlEditor"].Get('MESSAGE').SetContent(window["BXHtmlEditor"].Get('MESSAGE').GetContent()+$('#QuickResponse-'+params.selected).html());
			AltasibSupport.PopupMenu.ToggleMenu('support-change-qa-popup');
	  });    
	});  
	</script>
	<?endif;?>	
<?endif;?>
<span id="errors" style="display: none; color: red;"></span>
<a name="message"></a>
<?if($arParams["ID"]==0 && $arResult['SLA']):?>
<?ShowNote(GetMessage('ALTASIB_SUPPORT_EDIT_FORM_SLA',array('#SLA_NAME#'=>$arResult['SLA']['NAME'],'#SLA_TIME#'=>$arResult['SLA']['RESPONSE_TIME'])))?>
<?endif;?>

<?
if($arParams["ID"]>0 && isset($_SESSION["TICKET_MESSAGE_OK"]) &&$_SESSION["TICKET_MESSAGE_OK"])
{
    echo ShowNote(GetMessage('ALTASIB_SUPPORT_MESSAGE_ADD_OK'));
    unset($_SESSION["TICKET_MESSAGE_OK"]);
}

// echo '<pre>';

// $users = CGroup::GetListEx(Array(),Array(),0,0,Array('*'));
// $groupsArray = array();
// while($record = $users->fetch() ) {
// 	$groupsArray[$record['ID']][] = $record['USER_USER_ID'];
// }
// print_r($_REQUEST['section']);
// print_r($arResult['section']);

// echo '</pre>';
?>
<form name="ticket_add" id="ticket_add" action="<?=POST_FORM_ACTION_URI?>" method="post" enctype="multipart/form-data">
<input type="hidden" name="TICKET_ID" id="TICKET_ID" value="<?=$arParams["ID"];?>" />
<input type="hidden" name="PARRENT_MESSAGE_ID" value="<?=$arResult['PARRENT_MESSAGE_ID'];?>" />
<input type="hidden" name="SEND_MESSAGE" id="SEND_MESSAGE" value="Y" />
<input type="hidden" name="t_submit_go" id="t_submit_go" value="" />
<input type="hidden" name="section" id="section" value="<?php echo !empty($_REQUEST['section']) ? $_REQUEST['section'] : ''; ?>" />
<?=bitrix_sessid_post()?>
<div class="altasib_ticketnform" id='ticketn'>
<?if($arParams["ID"]==0 || ($arParams["ID"]>0 && $arParams["REPLY_ON"]=="Y" && !$arResult["IS_CLOSE"])){?>
	<?if($arParams["ID"]==0){?>
		<div class="dop_title_block_form"><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_ADD_NEW_MESSAGE")?></div>
		<?if(count($arParams['CUSTOMER_LIST'])):?>
		<div class="clear_col">
        	<div class="left_title_pole col_prop"><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_OWNER")?>:<span class="starrequired">*</span></div>
			<div class="addTicket-owner_chosen col_info">
					<select id="OWNER_ID" name="OWNER_ID" class="owner_id">
						<option value="0">-</option>
						<?foreach($arParams['CUSTOMER_LIST'] as $SID=>$sUser):?>
						<option value="<?=$SID?>" <?if($SID == $USER->GetID()):?>selected<?endif;?>><?=$sUser;?></option>
						<?endforeach;?>
					</select>
			</div>
        </div>
        <div class="clear_col">
        	<div class="left_title_pole col_prop">Контакты</div>
			<div class="col_info">
				<p><span class="prop-name">Почта:</span> mariya.ivantuk@betlab.com</p>
                <p><span class="prop-name">Телефон:</span> +38 (073) 000-00-00</p>
                <p><span class="prop-name">Департамент:</span> design_lab</p>
                <p><span class="prop-name">Должность:</span> web-дизайнер</p>
			</div>
        </div>
		<?endif;?>
		
		
		<div class="clear_col">
			<div class="left_title_pole col_prop">Источник обращения</div>
			<div class="addTicket-titleInputtext col_info">
					<select id="SOURCE_ID" name="SOURCE_ID" class="owner_id">
						<?foreach($arResult['SOURCE_ARRAY'] as $id => $value):?>
						<option value="<?=$id?>" <?if($arResult['SELECTED_SOURCE'] == $id):?>selected<?endif;?>><?=$value;?></option>
						<?endforeach;?>
					</select>
			</div>
		</div>
			
		<div class="clear_col">
            <div class="left_title_pole col_prop"><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_NAME")?>:<span class="starrequired">*</span></div>
    		<div class="addTicket-titleInputtext col_info">
    			<input type="text" name="TITLE" size="50" value="<?=$arResult["TITLE"];?>" class="inputtext"/>
    		</div>
        </div>
	
		<!-- 
		 <div class="dropdown">
		    <button class="btn btn-default dropdown-toggle" type="button" data-toggle="dropdown">Tutorials<span class="caret"></span></button>
		    
		    <ul class="dropdown-menu">
		    
		      <li><a tabindex="-1" href="#">HTML</a></li>
		      <li><a tabindex="-1" href="#">CSS</a></li>
		      <li class="dropdown-submenu"><a class="test" tabindex="-1" href="#">New dropdown <span class="caret"></span></a>
		      
		        <ul class="dropdown-menu">
		        
		          <li><a tabindex="-1" href="#">2nd level dropdown</a></li>
		          <li><a tabindex="-1" href="#">2nd level dropdown</a></li>
		          <li class="dropdown-submenu"><a class="test" href="#">Another dropdown <span class="caret"></span></a>
		          
		            <ul class="dropdown-menu">
		              <li><a href="#">3rd level dropdown</a></li>
		              <li><a href="#">3rd level dropdown</a></li>
		            </ul>
		          </li>
		        </ul>
		        
		      </li>
		    </ul>
		  </div>
		 -->
		
        <div class="clear_col">
            <div class="left_title_pole col_prop">Доп. информация</div>
            <div class="col_info">
    		<?php 
    		if ($section == 'AXO') {?>
    		    <div class="prop_wrap clear_col">
                    <div class="left_title_pole bl_prop">Адрес:</div>
            		<div class="addTicket-titleInputtext bl_info">
            			<input type="text" name="ADDRESS_NAME" size="50" value="<?=$arResult["ADDRESS_NAME"];?>" class="inputtext"/>
            		</div>
                </div>
    		 <?}
    		if ( !empty($_GET['section']) ) {
    		    
    		    $categoryDB = ALTASIB\Support\CategoryTable::getList();
    		    $categoryArray = array();
    		    while ($arCategory = $categoryDB->fetch()) {
    		        $categoryArray[$arCategory['DESCRIPTION']][$arCategory["ID"]] = $arCategory['NAME'];
    		    }
    		    
    		    $section = '';
    		    switch ((int)$_GET['section']) {
    	            case 1:
    	                $section = 'IT';
    	                break;
    	            case 2:
    	                $section = 'AXO';
    	                break;
    	            case 3:
    	                $section = 'HR';
    	                break;
    	        }
    		    
    		    $newArray = array();
    		    $newArrayNames = array();
    		    $newArrayIds = array();
    		    
    		    foreach ($categoryArray[$section] as $id => $value) {
    		        $subarray = array_map('intval', explode('.', $value));
    		        
    		        $value = trim(preg_replace('/[0-9]+./', '', $value));
    		        
    		        $newArrayNames[$id] = $value;
    		        
    		        if (is_numeric($subarray[0])) {
    		            if (!empty($subarray[1]) && is_numeric($subarray[1]) && $subarray[1] > 0) {
    		                if (!empty($subarray[2]) && is_numeric($subarray[2]) && $subarray[2] > 0) {
    		                    
    		                    $newArrayIds[$subarray[0]][$subarray[1]][$subarray[2]]['ID'] = $id;
    		                    
    		                    $index_1 = $newArrayIds[$subarray[0]]['ID'];
    		                    $index_2 = $newArrayIds[$subarray[0]][$subarray[1]]['ID'];
    		                    $index_3 = $newArrayIds[$subarray[0]][$subarray[1]][$subarray[2]]['ID'];
    		                    
    		                    $newArray[$index_1][$index_2][$index_3]['header'] = $value;
    		                } else {
    		                    $newArrayIds[$subarray[0]][$subarray[1]]['ID'] = $id;
    		                    
    		                    $index_1 = $newArrayIds[$subarray[0]]['ID'];
    		                    $index_2 = $newArrayIds[$subarray[0]][$subarray[1]]['ID'];
    		                    
    		                    $newArray[$index_1][$index_2]['header'] = $value;
    		                }
    		            } else {
    		                $newArrayIds[$subarray[0]]['ID'] = $id;
    		                
    		                $index_1 = $newArrayIds[$subarray[0]]['ID'];
    		                
    		                $newArray[$index_1]['header'] = $value;
    		            }
    		            
    		        }
    		    }
    		    
    		    $newArrayJson = json_encode($newArray);
    		    $newArrayNamesJson = json_encode($newArrayNames);
    		    
    // 		                echo '<pre>';
    // 		                print_r($newArray);
    // 		                print_r($arResult["CATEGORY"]);
    // 		                echo '</pre>';
    		    
    			
    			if ($_GET['section'] == $AXOSectionId) {
    				?>
    				<input type="hidden" name="CATEGORY_ID_1" value="23">
    				<?php
    			} elseif ($_GET['section'] == $ITSectionId) {
    				?>
    				<div class="prop_wrap col-sm-6 col-xs-12">
                        <div class="left_title_pole bl_prop"><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_CATEGORY")?>:<span class="starrequired">*</span></div>
        				<div class="addTicket-select bl_info">
        					<select name="CATEGORY_ID_1" class="owner_id" id="category_level_1">
        						<option value="0"> </option>
        						<?foreach($newArray as $categoryId => $categoryData):
        							$arCategory = $arResult["CATEGORY"][$categoryId];
        							$categoryName = $newArrayNames[$categoryId];
        							if ($arCategory["DESCRIPTION"] == 'IT') { ?>
        							<option value="<?=$arCategory["ID"]?>" <?if($arResult["CATEGORY_ID"]==$arCategory["ID"] || (!isset($arResult["CATEGORY_ID"]) && $arCategory["USE_DEFAULT"]=="Y"))echo "selected";?>><?=$categoryName;?></option>
        							<?
        							}
        						endforeach;?>
        					</select>
        					<select name="CATEGORY_ID_2" class="owner_id" id="category_level_2" style="display:none;">
        					</select>
        					<select name="CATEGORY_ID_3" class="owner_id" id="category_level_3" style="display:none;">
        					</select>
        				</div>
                    </div>
    				<?php
    			} elseif ($_GET['section'] == $HRSectionId) {
    				?>
    				<div class="prop_wrap col-sm-6 col-xs-12">
                        <div class="left_title_pole bl_prop"><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_CATEGORY")?>:<span class="starrequired">*</span></div>
        				<div class="addTicket-select bl_info">
        					<select name="CATEGORY_ID_1" class="owner_id" id="category_level_1">
        						<option value="0">- не вибрано -</option>
        						<?foreach($newArray as $categoryId => $categoryData):
        							$arCategory = $arResult["CATEGORY"][$categoryId];
        							$categoryName = $newArrayNames[$categoryId];
        							if ($arCategory["DESCRIPTION"] == 'HR') { ?>
        							<option value="<?=$arCategory["ID"]?>" <?if($arResult["CATEGORY_ID"]==$arCategory["ID"] || (!isset($arResult["CATEGORY_ID"]) && $arCategory["USE_DEFAULT"]=="Y"))echo "selected";?>><?=$categoryName;?></option>
        							<?
        							}
        						endforeach;?>
        					</select>
        					<select name="CATEGORY_ID_2" class="owner_id" id="category_level_2" style="display:none;">
        					</select>
        					<select name="CATEGORY_ID_3" class="owner_id" id="category_level_3" style="display:none;">
        					</select>
        				</div>
                    </div>
    				<?php
    			} else {
    				?>
    				<div class="prop_wrap col-sm-6 col-xs-12">
                        <div class="left_title_pole bl_prop"><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_CATEGORY")?>:<span class="starrequired">*</span></div>
        				<div class="addTicket-select bl_info">
        					<select name="CATEGORY_ID" class="owner_id">
        						<option value="0"> </option>
        						<?foreach($arResult["CATEGORY"] as $arCategory):?>
        						<option value="<?=$arCategory["ID"]?>" <?if($arResult["CATEGORY_ID"]==$arCategory["ID"] || (!isset($arResult["CATEGORY_ID"]) && $arCategory["USE_DEFAULT"]=="Y"))echo "selected";?>><?=$arCategory["NAME"];?></option>
        						<?endforeach;?>
        					</select>
        				</div>
    				</div>
    				<?php
    			}
    		}?>
    
        	<div class="prop_wrap col-sm-6 col-xs-12">
                <div class="left_title_pole bl_prop"><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_PRIORITY")?>:</div>
            	<div class="bl_info">
                  	<!-- <label class="btn btn-muted active" role="button">
                		<input type="radio" name="PRIORITY_ID" value="1">Низкий
                  	</label>
                	<label class="btn btn-muted" role="button">
                		<input type="radio" name="PRIORITY_ID" value="2">Нормальный
                  	</label>
                  	<label class="btn btn-muted" role="button">
                		<input type="radio" name="PRIORITY_ID" value="3">Высокий
                  	</label> -->
                    <select name="PRIORITY_ID">
						<option value="1">Низкий</option>
                        <option value="2">Нормальный</option>
                        <option value="3">Высокий</option>
					</select>
                </div>
            </div>
                                        
        	<?foreach($arParams["USER_FIELDS"] as $FIELD_NAME=>$arUserField):?>
        	<div class="prop_wrap col-sm-6 col-xs-12">
        		<div class="left_title_pole bl_prop"><?if ($arUserField["MANDATORY"]=="Y"):?><span class="requred_txt">*</span><?endif;?> <?=$arUserField["EDIT_FORM_LABEL"]?>:</div>
        		<div class="bl_info">
        			<?$APPLICATION->IncludeComponent(
        			   "bitrix:system.field.edit",
        			   $arUserField["USER_TYPE"]["USER_TYPE_ID"],
        			   array("bVarsFromForm" => $arResult, "arUserField" => $arUserField), null, array("HIDE_ICONS"=>"Y"));
        			?>
        		</div>
        	</div>
        	<?endforeach;?>  
            </div>
        </div>                              
    	<div><div class="lastPadding"></div></div>
    <?}?>
  <div class="clear_col">
    <?if($arParams["ID"]==0){?>
    <div class="col_prop">&nbsp;</div>
    <div class="col_info">
    <?}else{?>
    <div class="com_avatar"><div class="feed-com-avatar feed-com-avatar-N"><img src="/bitrix/images/1.gif" width="40" height="40"></div></div>
    <div class="com_wrap">
    <?}?>
        <div>
    		<div>
    		<?if($arParams["ID"]>0 && !$arParams['SHOW_FULL_FORM']):?>
    			<div class="support-form-note" id="divSupportFormShowNote"><div><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_ADD_MESSAGE")?></div></div>
    			<div id="divSupportFormShow" style="display:none;">                                        
    				<?ALTASIB\Support\Tools::ShowLHE("MESSAGE",$arResult["MESSAGE"],"MESSAGE");?>
    			</div>
    		<?else:?>
    		  <?ALTASIB\Support\Tools::ShowLHE("MESSAGE",$arResult["MESSAGE"],"MESSAGE");?>
    		<?endif;?>
    		</div>
    	</div>
    	<div class="support-form-tab">
    	<a onclick="BX.onCustomEvent(BX('support-form-loadFiles'), 'BFileDLoadFormController'); return false;" href="#" class="altasib-form-tab"><i class="icon-paper-clip"> </i><span><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_FILE")?></span></a>  
    	<?if($arParams["ID"]>0):?>
    		<a onClick = "AltasibSupport.Bar.ToggleBlock('altasib-param-form'); return false;" class="altasib-form-tab btn-params" href="#"><i class="icon-ok-sign"> </i><span><?=GetMessage("ALTASIB_SUPPORT_FORM_PARAM_ANSW")?></span></a> 
    	<?endif;?>
    		<div>
    			<div id="support-form-loadFiles"> <?
    				$APPLICATION->IncludeComponent("bitrix:main.file.input","drag_n_drop",Array(
    					"ALLOW_UPLOAD"=>"F",
    					"ALLOW_UPLOAD_EXT" => $arParams["UPLOAD_FILE_TYPE"],
    					//"MAX_FILE_SIZE" => $arParams["UPLOAD_FILE_SIZE"],
    					"INPUT_NAME"=>"FILES",
    					"INPUT_NAME_UNSAVED"=>"FILES_TMP",
    					"MULTIPLE"=>"Y",
    					"MODULE_ID"=>"altasib.support",
    					)
    				);
    				?>
    			</div>
    		</div>
    		
    	</div>
    
    	<?if($arParams["USE_CAPTCHA"] == "Y" && $arParams["ID"] == 0):?>
    		<div>
    			<div  class="left_title_pole"><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_CAPTCHA_TITLE")?>:</div>
    			<div>
    					<input type="hidden" name="captcha_sid" value="<?=$arResult["CAPTCHA_CODE"]?>" />
    					<img src="/bitrix/tools/captcha.php?captcha_sid=<?=$arResult["CAPTCHA_CODE"]?>" width="180" height="40" alt="CAPTCHA" />
    			</div>
    		</div>
    		<div>
    			<div  class="left_title_pole"><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_CAPTCHA_PROMPT")?><span class="starrequired">*</span>:</div>
    			<div><input type="text" class="inputtext" name="captcha_word" maxlength="50" value=""></div>
    		</div>
    	<?endif?>
    </div>
  </div>
<?}?>
<?if($arParams["ID"]==0){?>
<div class="clear_col">
	<div class="left_title_pole col_prop">&nbsp;</div>
	<div class="col_info add_item_btns">
		<input type="hidden" name="t_submit" id="t_submit" value="Y" />
		<a class="altasib-support-button" id="altasib-support-create-button" onclick="alSupForm.create();"><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_SUBMIT_CREATE")?></a>
		<a class="cancel-form" href="<?=$arParams["TICKET_LIST_URL"]?>"><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_SUBMIT_CANCEL")?></a>
	</div>
</div>
<?}?>
<?if($arParams["ID"]>0 && $arResult["IS_CLOSE"]){?>
	<div>
		<div  class="left_title_pole">&nbsp;</div>
		<div>
			<input type="hidden" name="OPEN" value="Y" />
			<input type="hidden" name="t_submit" id="t_submit" value="Y" />
			<a class="altasib-support-button" onclick="document.forms['ticket_add'].submit();"><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_OPEN")?></a>                                    
		</div>
	</div>
<?}elseif($arParams["ID"]>0 && $arParams["REPLY_ON"]=="Y"){?>
	<?//if($arParams['IS_SUPPORT_TEAM']):?>
	<div id="altasib-param-form">
	    <div class="dop_title_block_2"><?=GetMessage("ALTASIB_SUPPORT_FORM_PARAM_ANSW")?></div>		
		<div class="altasib-param-form-inner">
			<?if($arParams['IS_SUPPORT_TEAM']):?>							
			<div class="altasib-param-form1">
			<label><input type="checkbox" name="NOT_CHANGE" id="NOT_CHANGE" value="Y" /><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_NOT_CHANGE")?></label><br />
			<label><input type="checkbox" name="IS_HIDDEN" id="IS_HIDDEN" value="Y" /><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_HIDDEN")?></label>
			</div>
			<?endif;?>								
			<div class="altasib-param-form2">
				<label><input type="checkbox" name="IS_DEFERRED" value="Y" /><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_IS_DEFERRED_TITLE")?></label><br />
				<label><input type="checkbox" name="CLOSE" value="Y" /><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_CLOSE")?></label>								
			</div>
		</div>	
	</div>						

	<div class="send_btns">
			<div  class="left_title_pole">&nbsp;</div>
			<div>
			<a class="altasib-support-button" id="altasib-support-submit-form" href="#"><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_SUBMIT")?></a>
			<a id="altasib-support-submit-form-and-go" href="#"><?=GetMessage("ALTASIB_SUPPORT_EDIT_FORM_SUBMIT_AND_GO")?></a>
			<span class="span-cancel-form"><a href="#" id="cancel-form"><?=GetMessage('ALTASIB_SUPPORT_EDIT_FORM_SUBMIT_CANCEL')?></a></span>
			</div>
	</div>
<?}?>
</div>
</form>
<?if($arParams["ID"] == 0):?>
        <div class="altasib_support_edit_required"><?=GetMessage("ALTASIB_SUPPORT_EDIT_REQUIRED");?></div>
<?endif;?>		
        <?if(!$arParams['IS_SUPPORT_TEAM'] && $arParams['SHOW_GROUP_SELECTOR']):?>
        <div id="supportFormNote">
        <?=ShowNote(GetMessage('ALTASIB_SUPPORT_EDIT_FORM_GROUP_NOTE'));?>
        </div>
        <?endif;?>

<?endif;?>
</div>
<?if($arParams["ID"]>0 && $USER->IsAuthorized()):?>
<div class="to_list_wrap"><span class="back-arrow">&lt;</span> <a href="<?=$arParams["URL_LIST"]?>"><?=GetMessage('ALTASIB_SUPPORT_PATH_TO_LIST')?></a></div>
<?endif;?>
<script>
var categoryArray = '<?=$newArrayJson?>';
var newArrayNames = '<?=$newArrayNamesJson?>';

function numProps(obj) {
	  var c = 0;
	  for (var key in obj) {
	    if (obj.hasOwnProperty(key)) ++c;
	  }
	  return c;
}

if (categoryArray.length > 0) {
	categoryArray = JSON.parse(categoryArray);
}

if (newArrayNames.length > 0) {
	newArrayNames = JSON.parse(newArrayNames);
}


console.log(categoryArray);
console.log(newArrayNames);

$(document).ready(function(){
	
	if (window.location.pathname == '/support/ticket/0/') {
//		$(".btn-group-toggle").twbsToggleButtons();
	}	
	$(".nav-tabs a").click(function(){
	     $(this).tab('show');
	});
	
	$("#category_level_1").change(function(){
		console.log("#category_level_1");
		var categoryId = $(this).val();
		var singleCategoryArray = window.categoryArray[categoryId];
		
		if (numProps(singleCategoryArray) > 1) {
			$("#category_level_2").empty();
			$("#category_level_2").show();
			$("#category_level_2").append('<option value="">- не вибрано -</option>');

			$.each( singleCategoryArray, function( key, value ) {

				if(!isNaN(parseInt(key))) {
					$("#category_level_2").append('<option value="'+key+'">'+value['header']+'</option>');
				}
			});
			
		} else {
			$("#category_level_2").hide();
		}
	});

	$("#category_level_2").change(function(){
		console.log("#category_level_2");
		var categoryId = $(this).val();
		var parentId = $( "#category_level_1 option:selected" ).val();
		var singleCategoryArray = window.categoryArray[parentId][categoryId];
		
		if (numProps(singleCategoryArray) > 1) {
			$("#category_level_3").empty();
			$("#category_level_3").show();

			$("#category_level_3").append('<option value="">- не вибрано -</option>');
			$.each( singleCategoryArray, function( key, value ) {

				if(!isNaN(parseInt(key))) {
					$("#category_level_3").append('<option value="'+key+'">'+value['header']+'</option>');
				}
			});
			
		} else {
			$("#category_level_3").hide();
		}
	});
});

// $(document).ready(function(){
	


// 	$("#category_level_3").change(function(){
// 		var categoryId = $(this).val();
// 		console.log(categoryId);
// 	});
	
// });
</script>

