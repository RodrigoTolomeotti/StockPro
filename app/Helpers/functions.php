<?php

function validar_cpf($cpf) {

    if(empty($cpf))
          return false;

    $cpf = preg_replace('/[^0-9]/', '', $cpf);

    if (strlen($cpf) != 11)
        return false;

    if (preg_match('/(\d)\1{10}/', $cpf))
        return false;

    for ($t = 9; $t < 11; $t++) {

        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf{$c} * (($t + 1) - $c);
        }

        $d = ((10 * $d) % 11) % 10;

        if ($cpf{$c} != $d) {
            return false;
        }
    }

    return true;
}

function validar_cnpj($cnpj) {

    if(empty($cnpj))
        return false;

    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

    if (strlen($cnpj) != 14)
        return false;

    if (preg_match('/(\d)\1{13}/', $cnpj))
        return false;

    $b = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

    for ($i = 0, $n = 0; $i < 12; $n += $cnpj[$i] * $b[++$i]);

    if ($cnpj[12] != ((($n %= 11) < 2) ? 0 : 11 - $n)) {
        return false;
    }

    for ($i = 0, $n = 0; $i <= 12; $n += $cnpj[$i] * $b[$i++]);

    if ($cnpj[13] != ((($n %= 11) < 2) ? 0 : 11 - $n)) {
        return false;
    }

    return true;
}

function validar_cpf_cnpj($cpf_cnpj) {
    return 'regex:/(^\d{3}\.\d{3}\.\d{3}\-\d{2}$)|(^\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}$)/';
}

// Função criada para substituir tags HTML que o CKEditor coloca por padrão, além disto, também foi
// feito replace para possibilitar mensagens em itálico e negrito.
function substituirTagsWhatsapp($mensagem) {
    return str_replace([
        '<p>',
        '</p>',
        '<strong>',
        '</strong>',
        '<i>',
        '</i>',
        '&nbsp',
        ' ;'
    ], [
        '\n',
        '',
        '*',
        '*',
        '_',
        '_',
        ' ',
        ' ',
    ], $mensagem);
}
