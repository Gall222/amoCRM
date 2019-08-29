import $ from 'jquery';
import '../css/style.css';


$('input#input, textarea').hide();

$('.awesome_form').each(function () {
    let form = $(this);
    $('.text_input').append("<input class='awesome_input' id='x'>");  //создаем поля в div

    $('button').click(function () {

        //копируем значения в скрытые поля
        $("div#one input#input").val($("div#one input#x").val());
        $("div#two input#input").val($("div#two input#x").val());
        $("div#three textarea").val($("div#three input#x").val());

        //проверка форма
        let isCorrect = true;
        function finder() {
            return form.find('div.text_input input').each(function () {
                if ($(this).val() == '') {
                    alert("Заполните все поля формы");
                    isCorrect = false;
                    return false;
                }
            });
        }

        console.log(finder());
        if (isCorrect) {
            $.ajax({
                url: 'https://webhook.site/f3c008d6-3e0e-4f88-b43f-fd7f9fc9bda6',
                type: 'post',
                dataType: 'json',
                data: $('form#myForm').serialize(),
                success: function () {
                    alert("Данные отправлены");
                }
            });
        }
    });

});






