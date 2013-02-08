<?php
/**********************************************************************
    Copyright (C) FrontAccounting, LLC.
	Released under the terms of the GNU General Public License, GPL, 
	as published by the Free Software Foundation, either version 3 
	of the License, or (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
    See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
$page_security = 'SA_SALESBULKREP';
// ----------------------------------------------------------------
// $ Revision:	2.0 $
// Creator:	Joe Hunt
// date_:	2005-05-19
// Title:	Order Status List
// ----------------------------------------------------------------
$path_to_root="..";

include_once($path_to_root . "/includes/session.inc");
include_once($path_to_root . "/includes/date_functions.inc");
include_once($path_to_root . "/includes/data_checks.inc");
include_once($path_to_root . "/sales/includes/sales_db.inc");
include_once($path_to_root . "/inventory/includes/db/items_category_db.inc");

//----------------------------------------------------------------------------------------------------

//print_order_status_list();
print_csv_files();

//----------------------------------------------------------------------------------------------------

function GetSalesOrders($from, $to)
{
	$fromdate = date2sql($from);
	$todate = date2sql($to);

        $sql= "SELECT ".TB_PREF."nestle_codes.sys_code idProdDist, 
            ".TB_PREF."nestle_codes.description nombre, 
            ".TB_PREF."nestle_codes.item_code idProdNestle 
            FROM ".TB_PREF."nestle_codes 
            WHERE ".TB_PREF."nestle_codes.sys_code <> ''";

	return db_query($sql, "Error getting order details");
}

//----------------------------------------------------------------------------------------------------

function print_csv_files()
{

    global $path_to_root, $comp_path;

    $from = $_POST['PARAM_0'];
    $to = $_POST['PARAM_1'];
    //$destination = $_POST['PARAM_2'];

    $elCSV = '"idProdDist","nombre","idProdNestle"';
    $elCSV .= "\r\n";
        
    $csvName = "PR". str_replace('/','',$from) .".csv";
    $filepath = $comp_path.'/'.user_company().'/reporting/'.$csvName;
    $fileHandle = fopen($filepath, 'w') or die('Can\'t create .csv file, try again later.');
    
    //fputcsv($fileHandle, $headers);
    $result = GetSalesOrders($from, $to);

	while ($myrow=db_fetch($result))
	{
            
            $elCSV .= '"'.$myrow['idProdDist'].'","'.$myrow['nombre'].'","'.
                      $myrow['idProdNestle'].'"'."\r\n";
              
            //foreach ($elCSV as $fields){ 
            //    fputcsv($fileHandle, $fields,',','&quot;');
            //}
        }
        fwrite($fileHandle, $elCSV);
        fclose($fileHandle);
        
        meta_forward($_SERVER['PHP_SELF'], "csv=1&filename=$csvName&unique=$csvName");
        
}

//----------------------------------------------------------------------------------------------------

function print_order_status_list()
{
	global $path_to_root;

	$from = $_POST['PARAM_0'];
	$to = $_POST['PARAM_1'];
	$destination = $_POST['PARAM_2'];
        
	if ($destination)
		include_once($path_to_root . "/reporting/includes/excel_report.inc");
	else
		include_once($path_to_root . "/reporting/includes/pdf_report.inc");


	$cols = array(0, 60, 120, 180, 240, 300, 360, 420, 530);

	//$headers2 = array(_('Order'), _('Customer'), _('Branch'), _('Customer Ref'),
	//	_('Ord Date'),	_('Del Date'),	_('Loc'));

	$aligns = array('left',	'left',	'left', 'left', 'right', 'right', 'left', 'left');

	//$headers = array(_('Code'),	_('Description'), _('Ordered'),	_('Invoiced'),
	//	_('Outstanding'), '');
        $headers = array(_('idProdDist'), _('idTiendaDist'), _('idVendedor'), _('fecha'),
		_('costoUnitario'), _('cantidad'), _('idProdNestle'), _('idTiendaNestle'));

        //$params =   array( 	0 => array(  'text' => _('Period'), 'from' => $from, 'to' => $to));
        $params = array();
	

	$rep = new FrontReport(_('Reporte de Ventas diarias'), "VS", user_pagesize());
	$rep->Font();
	$rep->Info($params, $cols, $headers, $aligns);
        $rep->TextCol(0, 1, 'idProdDist');
        $rep->TextCol(1, 2, 'idTiendaDist');
        $rep->TextCol(2, 3, 'idVendedor');
        $rep->TextCol(3, 4, 'fecha');
        $rep->TextCol(4, 5, 'costoUnitario');
        $rep->TextCol(5, 6, 'cantidad');
        $rep->TextCol(6, 7, 'idProdNestle');
        $rep->TextCol(7, 8, 'idTiendaNestle');
        //$rep->NewLine(0,2,false);
	
	$orderno = 0;
        $total_vendedor = 0;

	$result = GetSalesOrders($from, $to);

	while ($myrow=db_fetch($result))
	{
		
		
		$rep->TextCol(0, 1,	$myrow['idProdDist']);
		$rep->TextCol(1, 2,	$myrow['idTiendaDist']);
                $rep->TextCol(2, 3,	$myrow['idVendedor']);
                $rep->TextCol(3, 4,	$myrow['fecha']);
		
		$rep->AmountCol(4, 5, $myrow['costoUnitario'], 2);
                $rep->AmountCol(5, 6, $myrow['cantidad'], 2);
                $rep->TextCol(6, 7, $myrow['idProdNestle']);
                $rep->TextCol(7, 8,	$myrow['idTiendaNestle']);
		$rep->NewLine();
	}
        
	$rep->End();
}

?>