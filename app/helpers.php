<?php
use App\Models\Menu;
use App\Models\Rolelink;
use App\Models\User;
use App\Models\LogActivity;

use Illuminate\Support\Facades\Auth;

// Get Parent Menus for Sidebar Menu
function getParentmenu()
{
    $menuList = Menu::where(['PARENT_CODE' => 0, 'ACTIVE_FLAG' => 'Y'])->orderBy('LINK_SEQUENCE', 'asc')->get();

    return $menuList;
}

function userMenu()
{
    $userId = Auth::id();

    $roleLinks = Rolelink::join('ctrl_user_role', 'ctrl_user_role.ROLE_ID', '=', 'ctrl_role_links.ROLE_ID')
        ->select('ctrl_role_links.LINK_CODE')
        ->where(['ctrl_user_role.LOGIN_ID' => $userId])
        ->get();

    $userLinks = DB::table('ctrl_user_links')->where(['LOGIN_ID' => $userId])->get();

    // Merge User Links and Role Links
    foreach ($userLinks as $val) {
        $roleLinks[] = $val;
    }

    $linkCodes = [];
    foreach ($roleLinks as $val) {
        $linkCodes[] = $val->LINK_CODE;
    }

    return $linkCodes;
}

function permission()
{
    $linkCodes = userMenu();
    
    $menuList = Menu::get();

    $linkPages = []; 
    foreach ($menuList as $val) {
        if(in_array($val->LINK_CODE,$linkCodes)){
            $linkPages[] = $val->LINK_PAGE;            
        }
    }
    return $linkPages;
}

function userRoleName()
{
    $userId = Auth::id();
    $roleId = DB::table('ctrl_user_role')->where(['LOGIN_ID'=>$userId])->first();

    $roledata = DB::table('mstr_role')->where(['ROLE_ID'=>$roleId->ROLE_ID])->first();
    
    return $roledata->ROLE_NAME;
}
function userName()
{
    $userId = Auth::id();
    $userName = User::where(['USER_ID'=>$userId])->first();
    return $userName->USER_NAME;
}

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