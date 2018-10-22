<?
#################################################
#   Company developer: ALTASIB                  #
#   Developer: Evgeniy Pedan                    #
#   Site: http://www.altasib.ru                 #
#   E-mail: dev@altasib.ru                      #
#   Copyright (c) 2006-2010 ALTASIB             #
#################################################
?>
<?if(!check_bitrix_sessid()) return;?>
<?
if($ex = $APPLICATION->GetException())
        echo CAdminMessage::ShowMessage(Array(
                "TYPE" => "ERROR",
                "MESSAGE" => GetMessage("MOD_INST_ERR"),
                "DETAILS" => $ex->GetString(),
                "HTML" => true,
        ));
else
        echo CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
?>
<form action="<?echo $APPLICATION->GetCurPage()?>">
        <input type="hidden" name="lang" value="<?echo LANG?>">
        <input type="submit" name="" value="<?echo GetMessage("MOD_BACK")?>">
<form>
