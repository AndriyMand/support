<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();?>
<?
$arParams['EDIT_URL'] =$arResult["FOLDER"].$arResult["URL_TEMPLATES"]["ticket_edit"];
?>
<h3>Выберите отдел для подачи заявки</h3>
<form action="">
    <button type="submit" class="tech-btn altasib-section-button <?php echo (!empty($_GET['section']) && $_GET['section'] == 1) ? 'section-button-active' : '';?>" name="section" value="1">Техподдержка пользователей</button>
    <button type="submit" class="task-btn altasib-section-button <?php echo (!empty($_GET['section']) && $_GET['section'] == 2) ? 'section-button-active' : '';?>" name="section" value="2">Заявки в АХО</button>
    <button type="submit" class="hr-btn altasib-section-button <?php echo (!empty($_GET['section']) && $_GET['section'] == 3) ? 'section-button-active' : '';?>" name="section" value="3">Обращения в HR</button>
</form>
<?

if ( !empty($_GET['section']) && in_array($_GET['section'], array(1, 2, 3)) ) {
    
    $arParams['section'] = $_GET['section'];
    $APPLICATION->IncludeComponent("altasib:support.ticket.list", "", 
        $arParams,
        $component
    );
}
?>