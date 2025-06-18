<?php

use App\Models\LogActivity;

use Illuminate\Support\Facades\Auth;


if(!function_exists('createLogActivity')) {
    function createLogActivity($module,$keyNo,$keyType, $userId){
        $date= now()->format('Y-m-d H:i:s');
        
            $logActivity = LogActivity::create([
                'MODULE' => $module,
                'KEY_NUMBER' => $keyNo,
                'KEY_TYPE' => $keyType,
                'ADDITION' => 'Y',
                'MODIFICATION' => 'N',                
                'CREATED_BY' => $userId,
                'CREATED_ON' => $date,
           ]);
        
        return $logActivity;
    }
}

if(!function_exists('modifyLogActivity')) {
    function modifyLogActivity($module,$keyNo,$keyType, $userId){
        $date= now()->format('Y-m-d H:i:s');
        
            $logActivity = LogActivity::create([
                'MODULE' => $module,
                'KEY_NUMBER' => $keyNo,
                'KEY_TYPE' => $keyType,
                'ADDITION' => 'N',
                'MODIFICATION' => 'Y',
                'CREATED_BY' => $userId,
                'CREATED_ON' => $date,
           ]);
        
        return $logActivity;
    }
}



?>