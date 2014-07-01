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
$route['default_controller'] = 'pesti_controller/view';
$route['login'] = 'user_controller/viewLogin';
$route['register'] = 'user_controller/viewRegister';
$route['insertClass'] = 'pesti_controller/viewInsertClass';
$route['insertClass/(:any)'] = 'pesti_controller/viewInsertClass/$1';

//Páginas De Inserção de Dados na Ontologia
$route['insertData/(:any)/(:any)/(:any)'] = 'pesti_controller/insertData/$1/$2/$3';
$route['insertProperty/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'pesti_controller/insertProperty/$1/$2/$3/$4/$5/$6';

//Páginas De Eliminação de Dados na Ontologia
$route['deleteData/(:any)/(:any)/(:any)'] = 'pesti_controller/deleteData/$1/$2/$3';
$route['deleteProperty/(:any)/(:any)/(:any)/(:any)/(:any)/(:any)'] = 'pesti_controller/deleteProperty/$1/$2/$3/$4/$5/$6';

//Páginas De listar informação da Ontologia
$route['listClasses/(:any)'] = 'pesti_controller/listClasses/$1';
$route['listSubClasses/(:any)'] = 'pesti_controller/listSubClasses/$1';
$route['getSubClasses/(:any)'] = 'pesti_controller/getSubClasses/$1';
$route['getMembers/(:any)/(:any)'] = 'pesti_controller/getMembers/$1/$2';
$route['getProperties'] = 'pesti_controller/getProperties';
$route['getPropertyRange/(:any)/(:any)'] = 'pesti_controller/getPropertyRange/$1/$2';
$route['getPropertyInfo/(:any)'] = 'pesti_controller/getPropertyInfo/$1';
$route['getClassProperty/(:any)'] = 'pesti_controller/getClassProperty/$1';
$route['getMemberProperty/(:any)'] = 'pesti_controller/getMemberProperty/$1';
$route['getComment/(:any)'] = 'pesti_controller/getComment/$1';
$route['selectSubClasses/(:any)'] = 'pesti_controller/selectSubClasses/$1';

//Outros
$route['printURI'] = 'pesti_controller/printURI';
$route['(:any)'] = 'pesti_controller/view/$1';

/* End of file routes.php */
/* Location: ./application/config/routes.php */