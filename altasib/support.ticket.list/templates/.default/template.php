    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
<?
#################################################
#   Company developer: ALTASIB                  #
#   Developer: Evgeniy Pedan                    #
#   Site: http://www.altasib.ru                 #
#   E-mail: dev@altasib.ru                      #
#   Copyright (c) 2006-2010 ALTASIB             #
#################################################
?>
<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="table_wrap">
<?if($arParams["HAS_CREATE"]):

$section = !empty($arResult["SECTION"]) ? '?section='.$arResult["SECTION"] : '';

if ($arParams['EXTENDED_FILTER']) {
?>
<form action="">
	<div class="head_btns">
        <a href="<?=$arParams["CREATE_URL"];?><?=$section;?>" class="altasib-support-button add_ticket"><i class="glyphicon glyphicon-plus-sign"></i> <?=GetMessage("ALTASIB_STL_T_CREATE_TICKET");?></a>
    	<button type="submit" class="btn btn-default btn-sm <?php echo (!empty($_GET['btn-filter']) && $_GET['btn-filter'] == 1) ? 'section-button-active' : '';?>" name="btn-filter" value="1"><span class="g-icon doing-icon"> Делаю</span></button>
    	<button type="submit" class="btn btn-default btn-sm <?php echo (!empty($_GET['btn-filter']) && $_GET['btn-filter'] == 2) ? 'section-button-active' : '';?>" name="btn-filter" value="2"><span class="g-icon save-icon"> Новые заявки</span></button>
    	<button type="submit" class="btn btn-default btn-sm <?php echo (!empty($_GET['btn-filter']) && $_GET['btn-filter'] == 3) ? 'section-button-active' : '';?>" name="btn-filter" value="3"><span class="g-icon inbox-icon"> Открытые</span></button>
    	<button type="submit" class="btn btn-default btn-sm <?php echo (!empty($_GET['btn-filter']) && $_GET['btn-filter'] == 4) ? 'section-button-active' : '';?>" name="btn-filter" value="4"><span class="g-icon briefcase-icon"> Все</span></button>
        <button type="submit" class="btn btn-default btn-sm <?php echo (!empty($_GET['btn-filter']) && $_GET['btn-filter'] == 6) ? 'section-button-active' : '';?>" name="btn-filter" value="6"><span class="g-icon report-icon"> Отчёты</span></button>
        
    	
    	<input type="hidden" name="section" value="<?php echo $arResult["SECTION"]; ?>">
    </div>
</form>
<?
} else {
?>
<form action="">
	<div class="head_btns">
        <a href="<?=$arParams["CREATE_URL"];?><?=$section;?>" class="altasib-support-button add_ticket"><?=GetMessage("ALTASIB_STL_T_CREATE_TICKET");?></a>
    	<button type="submit" class="btn btn-default btn-sm <?php echo (!empty($_GET['btn-filter']) && $_GET['btn-filter'] == 3) ? 'section-button-active' : '';?>" name="btn-filter" value="3"><span class="g-icon inbox-icon"> Открытые</span></button>
    	<button type="submit" class="btn btn-default btn-sm <?php echo (!empty($_GET['btn-filter']) && $_GET['btn-filter'] == 5) ? 'section-button-active' : '';?>" name="btn-filter" value="5"><span class=""><i class="glyphicon glyphicon-ok"></i> Закрытые</span></button>
    	<button type="submit" class="btn btn-default btn-sm <?php echo (!empty($_GET['btn-filter']) && $_GET['btn-filter'] == 4) ? 'section-button-active' : '';?>" name="btn-filter" value="4"><span class="g-icon briefcase-icon"> Все</span></button>
    	
        
        <input type="hidden" name="section" value="<?php echo $arResult["SECTION"]; ?>">
    </div>
</form>
<?	
}

?>

<?endif;?>
<?
$gridRows = array();

$categoryDB = ALTASIB\Support\CategoryTable::getList();
$categoryArray = array();
while ($arCategory = $categoryDB->fetch()) {
	$categoryArray[$arCategory['DESCRIPTION']][$arCategory["ID"]] = $arCategory['NAME'];
}

$section = '';
if (!empty($_GET['section'])) {
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
}



$newArray = array();
foreach ($categoryArray[$section] as $value) {
	$subarray = array_map('intval', explode('.', $value));

	if (is_numeric($subarray[0])) {
		if (!empty($subarray[1]) && is_numeric($subarray[1]) && $subarray[1] > 0) {
			if (!empty($subarray[2]) && is_numeric($subarray[2]) && $subarray[2] > 0) {
				$newArray[$subarray[0]][$subarray[1]][$subarray[2]]['header'] = trim(preg_replace('/[0-9]+./', '', $value));
			} else {
				$newArray[$subarray[0]][$subarray[1]]['header'] = trim(preg_replace('/[0-9]+./', '', $value));
			}
		} else {
			$newArray[$subarray[0]]['header'] = trim(preg_replace('/[0-9]+./', '', $value));
		}

	}
}

if ($section == 'AXO') {
	foreach ($arParams['GRID']['HEADER'] as $index => $header) {
		if ($header['id'] == 'CATEGORY') {
			unset($arParams['GRID']['HEADER'][$index]);
		}
	}
}


foreach ($arResult["TICKET"] as $arTicket)
{
    //$overdue = ($arParams['SUPPORT_TEAM'] && $arTicket['IS_OVERDUE']=='Y') ? ' <span style="color: red; font-size: smaller;">'.GetMessage('ALTASIB_SUPPORT_LIST_IS_OVERDUE').'</span>' : '';
    $overdue = ($arParams['SUPPORT_TEAM'] && $arTicket['IS_OVERDUE']=='Y') ? ' <span class="overdue_icon"></span>' : '';
    $color = '<div class="altasib-support-block-'.$arTicket['COLOR'].'"></div>';
    

    
    $categoryLevels = array_map('intval', explode('.', $arTicket["CATEGORY_NAME"]));
    $categoryLevels = array_filter($categoryLevels);
    
    $newCategoryName = '';
    
    $categoryTree = $newArray;
    
    foreach ($categoryLevels as $levelIndex) {
    	$newCategoryName .= $categoryTree[$levelIndex]['header'] . ' > ';
    	$categoryTree = $categoryTree[$levelIndex];
    }
    
    $arTicket["CATEGORY_NAME"] = rtrim(rtrim(rtrim($newCategoryName), ">"));
    
//     echo '<pre>';
//     print_r($arTicket);
//     echo '</pre>';
    
    $cols = array(
        'ID' => '<a href="'.$arTicket["URL_DETAIL"].'"'.(($arTicket['LAST_MESSAGE_BY_SUPPORT']=='N' && $arParams['SUPPORT_TEAM']) ? 'style="color:red"' : '').'>'.$arTicket["ID"].'</a>',
        'TITLE' => '<a href="'.$arTicket["URL_DETAIL"].'">'.$arTicket["TITLE"].'</a>',
        'CATEGORY'=> $overdue.$arTicket["CATEGORY_NAME"],
        'STATUS' => $color.'<span class="sts_txt">'.$arTicket["STATUS_NAME"].'</status>',
        'RESPONSIBLE' => !empty($arTicket['RESPONSIBLE_USER_SHORT_NAME']) ? $arTicket['RESPONSIBLE_USER_SHORT_NAME'] : (($arResult['isInSection']) ? '<a href="/support/ticket/'.$arTicket["ID"].'/?take-over=1" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-triangle-right"> Взять в работу</span></a>' : ''),
        'OWNER' => $arTicket['OWNER_USER_SHORT_NAME'],
        'PRIORITY' => $arTicket['PRIORITY_ID']>0 ? '<span class="prior_bord">'.\ALTASIB\Support\Priority::getName($arTicket['PRIORITY_ID']).'</span>' : '',
        'CREATED_USER' => $arTicket['CREATED_USER_SHORT_NAME'],
        'MODIFIED_USER' => $arTicket['MODIFIED_USER_SHORT_NAME'],
        'LAST_MESSAGE_USER' => $arTicket['LAST_MESSAGE_USER_SHORT_NAME'],
        'SLA' => $arTicket['SLA_NAME'],
        'PROJECT_ID' => $arTicket['PROJECT_NAME'],
    );
    
    $gridRows[] = array("data"=>$arTicket, "actions"=>$aActions, "columns"=>$cols, "editable"=>false);    
}

// echo '<pre>';
// print_r($arParams['GRID']['HEADER']);
// echo '</pre>';

$APPLICATION->IncludeComponent(
   "bitrix:main.interface.grid",
   "",
   array(
      "GRID_ID"=>$arParams['GRID_ID'],
      "HEADERS"=>$arParams['GRID']['HEADER'],
      "SORT"=>$arResult["SORT"],
      "SORT_VARS"=>$arResult["SORT_VARS"],
      "ROWS"=>$gridRows,
      "FOOTER"=>array(array("title"=>GetMessage('ALTASIB_STL_T_LIST_ALL'), "value"=>$arResult["NAV_OBJECT"]->NavRecordCount)),
      "ACTION_ALL_ROWS"=>false,
      "EDITABLE"=>false,
      "NAV_OBJECT"=>$arResult["NAV_OBJECT"],
      "AJAX_MODE"=>"N",
      "AJAX_OPTION_JUMP"=>"N",
      "AJAX_OPTION_STYLE"=>"Y",
      "FILTER"=>$arResult['GRID_FILTER'],
/*      '~FILTER_ROWS'=>array('TITLE'),*/
      "USE_THEMES"=>false
   )
);    
?>
</div>
<div style="display: table;padding-top: 10px;">
    <?if($arParams['IS_SUPPORT_TEAM']):?>
    <div style="display: table-row;">
        <div style="display: table-cell;"><div class="marker-clr altasib-support-block-red"></div></div>
        <div style="display: table-cell;"> - <?=GetMessage('ALTASIB_SUPPORT_LIST_LAST_MESSAGE_CLIENT_SUP')?></div>
    </div>
    <div style="display: table-row;">
        <div style="display: table-cell;"><div class="marker-clr altasib-support-block-green"></div></div>
        <div style="display: table-cell;"> - <?=GetMessage('ALTASIB_SUPPORT_LIST_LAST_MESSAGE_SUPPORT')?></div>
    </div>    
    <?else:?>
    <div style="display: table-row;">
        <div style="display: table-cell;"><div class="marker-clr altasib-support-block-red"></div></div>
        <div style="display: table-cell;"> - <?=GetMessage('ALTASIB_SUPPORT_LIST_LAST_MESSAGE_SUPPORT')?></div>
    </div>
    <div style="display: table-row;">
        <div style="display: table-cell;"><div class="marker-clr altasib-support-block-green"></div></div>
        <div style="display: table-cell;"> - <?=GetMessage('ALTASIB_SUPPORT_LIST_LAST_MESSAGE_CLIENT')?></div>
    </div>
    <?endif;?>
    <div style="display: table-row;">
        <div style="display: table-cell;"><div class="marker-clr altasib-support-block-brown"></div></div>
        <div style="display: table-cell;"> - <?=GetMessage('ALTASIB_SUPPORT_LIST_IS_DEFERRED')?></div>
    </div>
    <div style="display: table-row;">
        <div style="display: table-cell;"><div class="marker-clr altasib-support-block-gray"></div></div>
        <div style="display: table-cell;"> - <?=GetMessage('ALTASIB_SUPPORT_LIST_LAST_MESSAGE_CLOSE')?></div>
    </div>
</div>

<script>
    $('.add_ticket').prependTo('.pagetitle-inner-container');
    $('table.bx-grid-sorting td:contains("Сообщений")').html('<i class="td_mess"></i>');
</script>
