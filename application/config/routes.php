<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');
/*
  | -------------------------------------------------------------------------
  | URI ROUTING
  | -------------------------------------------------------------------------
  | This file lets you re-map URI requests to specific controller functions.
  |
  | Typically there is a one-to-one relationship between a URL string
  | and its corresponding controller class/method. The segments in a
  | URL normally follow this pattern:
  |
  |	example.com/class/method/id/
  |
  | In some instances, however, you may want to remap this relationship
  | so that a different class/function is called than the one
  | corresponding to the URL.
  |
  | Please see the user guide for complete details:
  |
  |	http://codeigniter.com/user_guide/general/routing.html
  |
  | -------------------------------------------------------------------------
  | RESERVED ROUTES
  | -------------------------------------------------------------------------
  |
  | There area two reserved routes:
  |
  |	$route['default_controller'] = 'welcome';
  |
  | This route indicates which controller class should be loaded if the
  | URI contains no data. In the above example, the "welcome" class
  | would be loaded.
  |
  |	$route['404_override'] = 'errors/page_missing';
  |
  | This route will tell the Router what URI segments to use if those provided
  | in the URL cannot be matched to a valid route.
  |
 */

//Páginas De Visualização
$route['default_controller'] = 'main_controller/view';
$route['login'] = 'user_controller/viewLogin';
$route['register'] = 'user_controller/viewRegister';
$route['insertClass'] = 'insert_controller/viewInsertClass';
$route['insertClass/(:any)'] = 'insert_controller/viewInsertClass/$1';
$route['admin'] = 'user_controller/viewAdmin';

//Páginas De listar informação da Ontologia
$route['listClasses/(:any)'] = 'consult_controller/listClasses/$1';
$route['listSubClasses/(:any)/(:any)'] = 'consult_controller/listSubClasses/$1/$2';
$route['getSubClasses/(:any)/(:any)'] = 'consult_controller/getSubClasses/$1/$2';
$route['getMembers/(:any)/(:any)'] = 'consult_controller/getMembers/$1/$2';
$route['getProperties'] = 'consult_controller/getProperties';
$route['getPropertyRange/(:any)/(:any)'] = 'consult_controller/getPropertyRange/$1/$2';
$route['getPropertyInfo/(:any)'] = 'consult_controller/getPropertyInfo/$1';
$route['getClassProperty/(:any)/(:any)'] = 'consult_controller/getClassProperty/$1/$2';
$route['getMemberProperty/(:any)/(:any)'] = 'consult_controller/getMemberProperty/$1/$2';
$route['getComment/(:any)'] = 'consult_controller/getComment/$1';
$route['selectSubClasses/(:any)'] = 'consult_controller/selectSubClasses/$1';

//Páginas De Inserção de Dados na Ontologia
$route['insertData/(:any)/(:any)/(:any)'] = 'insert_controller/insertData/$1/$2/$3';
$route['insertProperty/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'insert_controller/insertProperty/$1/$2/$3/$4/$5/$6';

//Páginas De Eliminação de Dados na Ontologia
$route['deleteData/(:any)/(:any)/(:any)'] = 'delete_controller/deleteData/$1/$2/$3';
$route['deleteProperty/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'delete_controller/deleteProperty/$1/$2/$3/$4/$5/$6';

//Páginas relacionadas com utilizadores
$route['listUsers'] = 'user_controller/listUsers';
$route['checkUser/(:any)/(:any)'] = 'user_controller/checkUserExists/$1/$2';
$route['checkUserPassword/(:any)/(:any)'] = 'user_controller/checkUserPassword/$1/$2';
$route['getUserAccessLevel/(:any)'] = 'user_controller/getUserAccessLevel/$1';
$route['insertNewUser/(:any)/(:any)'] = 'user_controller/insertNewUser/$1/$2';
$route['deleteUser/(:any)'] = 'user_controller/deleteUser/$1';

//Página de testes
$route['tests'] = 'test_controller/tests';

//Outros
$route['printURI'] = 'consult_controller/printURI';
$route['getFusekiUserTDB'] = 'user_controller/getFusekiAddress';
$route['getFusekiTDB'] = 'main_controller/getFusekiAddress';
$route['checkFusekiUserTDB'] = 'user_controller/checkFusekiStatus';
$route['checkFusekiTDB'] = 'main_controller/checkFusekiStatus';
$route['(:any)'] = 'main_controller/view/$1';

/* End of file routes.php */
/* Location: ./application/config/routes.php */