<?php
namespace Parzibyte;

class Validator
{
    public static function validateOrRedirect($data, $rules, $route = null)
    {
        $validator = new \Valitron\Validator($data);
        $validator->rules($rules);
        if (!$validator->validate()) {
            $redirect = Redirect::with([
                "errores_formulario" => $validator->errors(),
            ]);
            if ($route != null) {
                $redirect->to($route);
            } else {
                $redirect->back();
            }
            $redirect->do();
        }
    }
}
