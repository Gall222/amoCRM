<?php
require_once(__DIR__.'\data.php');

$user = new Form();
$user->isDuplicate($_POST);





?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
<!--    --><?php //require_once(__DIR__.'\data.php');?>
</head>
<body>
<form name="form" method="post" onsubmit="return validate_form();">
    <p><input name="nameOfSale" placeholder="Название сделки"></p>
    <p><input name="cost" placeholder="Сумма сделки"></p>
    <p><input name="contactName" placeholder="Имя клиента"></p>
    <p><input name="contactPost" placeholder="Должность"></p>
    <p><textarea name="customField" placeholder="Дополнительно"></textarea></p>
    <p><button type="submit">Отправить</button></p>
</form>
<script>
    function validate_form(){
        if (document.form.contactName.value == "" || document.form.cost.value == "" || document.form.nameOfSale.value == ""
            || document.form.customField.value == "" )
        {
            alert("Заполните все поля формы");
            return false;
        }
    else
        {
            return true;
        }
    }
</script>
</body>
</html>

