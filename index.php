<?php require 'fibonacci.php'; ?>

<form method="post" action="">
    <label for="f_num">Numer ciągu: </label>
    <input type="text" name="f_num">
    <label for="f_function">Funkcja: </label>
    <select name="f_function">
        <option value="s_recursive" selected="selected">recursive</option>
        <option value="s_iterative" selected="selected">iterative</option>
        <option value="s_optimized" selected="selected">optimized</option>
    </select>
    <input type="submit" name="submit" value="Pokaż">
</form>

<?php

if(isset($_POST['submit'])){

    $function = $_POST['f_function'];
    $num = $_POST['f_num'];

    if(!preg_match('/^\d+$/', $num) || $num > 1000) {
        echo 'Nieprawidłowa wartość!';
        exit();
    }

    switch($function){
        case 's_recursive':
            echo $function.": ".call_user_func('Fibonacci::recursive', $num);
            break;
        case 's_iterative':
            echo $function.": ".call_user_func('Fibonacci::iterative', $num);
            break;
        case 's_optimized':
            echo $function.": ".call_user_func('Fibonacci::optimized', $num);
            break;
    }
}

?>