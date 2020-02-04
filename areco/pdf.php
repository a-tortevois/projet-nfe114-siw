<?php 
require_once("_realpath.php");
require_once($path."config.php");
require_once($path."/lib/fpdf/fpdf.php");
require_once($path."/lib/fpdf/class.pdf.php");

DEFINE('BORDERDEBUG', 0);
DEFINE('INIT_Y', 32);
DEFINE('LH', 6);
$ini = read_file($path."/btp/btp.ini");
$mysqli = new My_SQL();

// Language file
if( !isset($_GET['lang']) || !file_exists( "pdf_lang_".$_GET['lang'].".php" ) )
{
	echo 'Error : argument LANG is not valid in script pdf.php';
	exit;
}
require_once("pdf_lang_".$_GET['lang'].".php");

if( !isset($_GET['id']) || !is_numeric($_GET['id']) )
{
	echo 'Error : argument ID is not valid in script pdf.php';
	exit;
}

function get_result( $result )
{
	$a = array();
	$a[0] = ($result == 1) ? 'ok' : 'nok';
	$a[1] = ($result == 1) ? 'green' : 'red';
	
	return $a;
}

function get_result_ventil( $cur_ventil1, $cur_ventil2 )
{
	global $ini;
	if( is_between($cur_ventil1, $ini['cur_ventil1_min'], $ini['cur_ventil1_max']) && is_between($cur_ventil2, $ini['cur_ventil2_min'], $ini['cur_ventil2_max']) )
	{
		return 1;
	}
	return 0;
}

function get_result_heating( $T, $T_max, $time, $time_min )
{
	if( $T >= $T_max && $time >= $time_min  )
	{
		return 1;
	}
	return 0;
}

$sql = 'SELECT 
			btp_machine.num_serie AS serial_machine,
			btp_carte.num_serie AS serial_carte,
			btp_carte.addr_mac,
			btp_result.*
		FROM btp_result, btp_machine, btp_carte
		WHERE 
			btp_machine.id_machine = '.$_GET['id'].'
		AND
			btp_carte.id_carte = btp_machine.id_carte
		AND
			btp_result.id_machine = '.$_GET['id'];
$req_1 = $mysqli->my_query($sql);

$sql = 'SELECT 
			MIN(board_vol) AS board_vol_min, 
			MAX(board_vol) AS board_vol_max, 
			AVG(board_vol) AS board_vol_avg, 
			MIN(piezo_cur) AS piezo_cur_min, 
			MAX(piezo_cur) AS piezo_cur_max, 
			AVG(piezo_cur) AS piezo_cur_avg, 
			MIN(piezo_vol) AS piezo_vol_min, 
			MAX(piezo_vol) AS piezo_vol_max, 
			AVG(piezo_vol) AS piezo_vol_avg, 
			MIN(piezo_freq) AS piezo_freq_min, 
			MAX(piezo_freq) AS piezo_freq_max, 
			AVG(piezo_freq) AS piezo_freq_avg, 
			MIN(ctn) AS ctn_min, 
			MAX(ctn) AS ctn_max, 
			AVG(ctn) AS ctn_avg, 
			MIN(pump_freq) AS pump_freq_min, 
			MAX(pump_freq) AS pump_freq_max, 
			AVG(pump_freq) AS pump_freq_avg
		FROM btp_monitor WHERE id_machine = '.$_GET['id'];
$req_2 = $mysqli->my_query($sql);
	
if( $mysqli->get_flag_error() == 0 )
{		
	$result = $req_1->fetch_array();
	// echo '<pre>';
	// print_r( $result );
	// echo '</pre>';
	
	$monitor = $req_2->fetch_array();
	
	// Construire le tableau de résultat
	$a_result = array();
	$a_result['in_hygro'] = ($result['in_hygro'] == 1) ? 1 : 0;
	$a_result['out_opt'] = ($result['out_opt'] == 1) ? 1 : 0;
	$a_result['in_ana1'] = 1; // TODO v > 616
	$a_result['in_ana2'] = 1; // TODO v > 616
	// $a_result['cur_ventil'] = get_result_ventil( $result['cur_ventil1'], $result['cur_ventil2'] ); // v > 616
	$a_result['cur_ventil'] = 1;
	$a_result['chauf1'] = get_result_heating( $result['chauf1_Tmax'], $ini['chauf1_Tmax'], $result['chauf1_time'], $ini['chauf1_time'] );
	$a_result['board_vol'] = is_between( $monitor['board_vol_avg'], $ini['board_vol_min'], $ini['board_vol_max'] );
	$a_result['piezo_cur'] = is_between( $monitor['piezo_cur_avg'], $ini['piezo_cur_min'], $ini['piezo_cur_max'] );
	$a_result['piezo_vol'] = is_between( $monitor['piezo_vol_avg'], $ini['piezo_vol_min'], $ini['piezo_vol_max'] );
	$a_result['piezo_freq'] = is_between( $monitor['piezo_freq_avg'], $ini['piezo_freq_min'], $ini['piezo_freq_max'] );
	$a_result['ctn'] = is_between( $monitor['ctn_avg'], $ini['ctn_min'], $ini['ctn_max'] );
	$a_result['pump_freq'] = is_between( $monitor['pump_freq_avg'], $ini['pump_freq_min'], $ini['pump_freq_max'] );
	// $a_result['board_vol'] = is_between_2_values( $monitor['board_vol_min'], $ini['board_vol_min'], $monitor['board_vol_max'], $ini['board_vol_max'] );
	// $a_result['piezo_cur'] = is_between_2_values( $monitor['piezo_cur_min'], $ini['piezo_cur_min'], $monitor['piezo_cur_max'], $ini['piezo_cur_max'] );
	// $a_result['piezo_vol'] = is_between_2_values( $monitor['piezo_vol_min'], $ini['piezo_vol_min'], $monitor['piezo_vol_max'], $ini['piezo_vol_max'] );
	// $a_result['piezo_freq'] = is_between_2_values( $monitor['piezo_freq_min'], $ini['piezo_freq_min'], $monitor['piezo_freq_max'], $ini['piezo_freq_max'] );
	// $a_result['ctn'] = is_between_2_values( $monitor['ctn_min'], $ini['ctn_min'], $monitor['ctn_max'], $ini['ctn_max'] );
	// $a_result['pump_freq'] = is_between_2_values( $monitor['pump_freq_min'], $ini['pump_freq_min'], $monitor['pump_freq_max'], $ini['pump_freq_max'] );
	$a_result['chauf2'] = ( get_result_heating( $result['chauf2_Tmax'], $ini['chauf2_Tmax'], $result['chauf2_time'], $ini['chauf2_time'] ) && $result['chauf2_bilame'] > $ini['chauf2_bilame'] ) ? 1 : 0;
	// <--

	$pdf = new PDF();
	$pdf->SetTitle($result['serial_machine']);
	$pdf->SetAuthor('ARECO');
	$pdf->SetCreator('ARECO');
	$pdf->AliasNbPages();
	$pdf->AddPage();

	$pdf->AddLine($label['serial_machine'], $result['serial_machine']);
	$pdf->AddLine($label['serial_carte'], format_serial_carte($result['serial_carte']).' / '.$result['addr_mac']);
	$pdf->AddLine($label['date_start'], $result['date_start']);
	$pdf->AddLine($label['op_in'], $result['op_in']);
	$pdf->AddLine($label['op_out'], $result['op_out']);
	$pdf->AddLine($label['date_nebu'], $result['date_nebu'].' / '.$result['date_end']);
	$pdf->AddLine($label['time_ceram'], $result['time_ceram'].'s');
	$pdf->AddLine($label['time_pump'], $result['time_pump'].'s');
	$pdf->AddLine($label['sw_version'], $result['sw_version']);
	$y = $pdf->Get_Y();
	
	$pdf->TestResult( in_array(0, $a_result) );
	
	$y += LH;
	$pdf->Set_Y($y);

	list($_lib, $_color) = get_result( $result['in_hygro'] );
	$pdf->AddLine($label['in_hygro'], $label[$_lib], $_color);
	list($_lib, $_color) = get_result( $result['out_opt'] );
	$pdf->AddLine($label['out_opt'], $label[$_lib], $_color);

	// Entrées analogiques
	$pdf->AddLine($label['in_ana']);
	$y = $pdf->Get_Y();

	$pdf->SetXY(30,$y);
	$pdf->SetFont('Arial','',11);
	$pdf->Cell(30, LH, '', 0, 0, 'C');
	$pdf->Cell(30, LH, '0%', 1, 0, 'C');
	$pdf->Cell(30, LH, '50%', 1, 0, 'C');
	$pdf->Cell(30, LH, '100%', 1, 0, 'C');
	$pdf->Cell(30, LH, $label['result'], 1, 0, 'C');
	$y += LH;

	$pdf->SetXY(30,$y);
	$pdf->Cell(30, LH, 'ANA_1', 1, 0, 'C');
	$pdf->Cell(30, LH, $result['in_ana1_0'].' V', 1, 0, 'C');
	$pdf->Cell(30, LH, $result['in_ana1_1'].' V', 1, 0, 'C');
	$pdf->Cell(30, LH, $result['in_ana1_2'].' V', 1, 0, 'C');
	// TODO --> 
	$_lib = 'ok';
	$_color = 'green';
	$pdf->CellResult( $label[$_lib], $_color );
	// TODO <--
	$y += LH;

	$pdf->SetXY(30,$y);
	$pdf->Cell(30, LH, 'ANA_2', 1, 0, 'C');
	$pdf->Cell(30, LH, $result['in_ana2_0'].' V', 1, 0, 'C');
	$pdf->Cell(30, LH, $result['in_ana2_1'].' V', 1, 0, 'C');
	$pdf->Cell(30, LH, $result['in_ana2_2'].' V', 1, 0, 'C');
	// TODO --> 
	$_lib = 'ok';
	$_color = 'green';
	$pdf->CellResult( $label[$_lib], $_color );
	// TODO <-- 
	$y += LH*2;
	$pdf->Set_Y($y);
	// <--
	
	// Ventilateur
	$pdf->AddLine($label['fan']);
	$y = $pdf->Get_Y();

	$pdf->SetXY(60,$y);
	$pdf->SetFont('Arial','',11);
	$pdf->Cell(30, LH, '50%', 1, 0, 'C');
	$pdf->Cell(30, LH, '100%', 1, 0, 'C');
	$pdf->Cell(30, LH, $label['result'], 1, 0, 'C');
	$y += LH;

	$pdf->SetXY(60,$y);
	$pdf->Cell(30, LH, $result['cur_ventil1'].' V', 1, 0, 'C');
	$pdf->Cell(30, LH, $result['cur_ventil2'].' V', 1, 0, 'C');
	list($_lib, $_color) = get_result( $a_result['cur_ventil'] );
	// TODO --> 	
	$_lib = 'ok';
	$_color = 'green';
	// TODO <-- 	
	$pdf->CellResult( $label[$_lib], $_color );
	$y += LH*2;
	$pdf->Set_Y($y);
	// <--
	
	// Chauffe 1
	list($_lib, $_color) = get_result( $a_result['chauf1'] );
	$pdf->AddLine($label['heating_1'], $label[$_lib], $_color);
	$y = $pdf->Get_Y();

	$pdf->SetXY(30,$y);
	$pdf->SetFont('Arial','',11);
	$pdf->Cell(60, LH, $label['hour'], 1, 0, 'C');
	$pdf->Cell(30, LH, $label['timer'], 1, 0, 'C');
	$pdf->Cell(30, LH, $label['tmax'], 1, 0, 'C');
	$pdf->Cell(30, LH, $label['bilame'], 1, 0, 'C');
	$y += LH;

	$pdf->SetXY(30,$y);
	$pdf->SetFont('Arial','',11);
	$pdf->Cell(60, LH, $result['chauf1_start'], 1, 0, 'C');
	$pdf->Cell(30, LH, $result['chauf1_time'].'s', 1, 0, 'C');
	$pdf->Cell(30, LH, $result['chauf1_Tmax'].'°C', 1, 0, 'C');
	$pdf->Cell(30, LH, $result['chauf1_bilame'].'s', 1, 0, 'C');
	$y += LH*2;
	$pdf->Set_Y($y);
	// <--

	// Monitor
	$pdf->AddLine($label['monitor']);
	$y = $pdf->Get_Y();

	$pdf->SetXY(60,$y);
	$pdf->SetFont('Arial','',11);
	$pdf->Cell(30, LH, $label['min'], 1, 0, 'C');
	$pdf->Cell(30, LH, $label['moy'], 1, 0, 'C');
	$pdf->Cell(30, LH, $label['max'], 1, 0, 'C');
	$pdf->Cell(30, LH, $label['result'], 1, 0, 'C');
	$y += LH;

	$pdf->SetXY(30,$y);
	$pdf->SetFont('Arial','',11);
	$pdf->Cell(30, LH, $label['board_vol'], 1, 0, 'C');
	$pdf->Cell(30, LH, $monitor['board_vol_min'], 1, 0, 'C');
	$pdf->Cell(30, LH, round($monitor['board_vol_avg']), 1, 0, 'C');
	$pdf->Cell(30, LH, $monitor['board_vol_max'], 1, 0, 'C');
	list($_lib, $_color) = get_result( $a_result['board_vol'] );
	$pdf->CellResult( $label[$_lib], $_color );
	$y += LH;

	$pdf->SetXY(30,$y);
	$pdf->SetFont('Arial','',11);
	$pdf->Cell(30, LH, $label['piezo_cur'], 1, 0, 'C');
	$pdf->Cell(30, LH, $monitor['piezo_cur_min'], 1, 0, 'C');
	$pdf->Cell(30, LH, round($monitor['piezo_cur_avg']), 1, 0, 'C');
	$pdf->Cell(30, LH, $monitor['piezo_cur_max'], 1, 0, 'C');
	list($_lib, $_color) = get_result( $a_result['piezo_cur'] );
	$pdf->CellResult( $label[$_lib], $_color );
	$y += LH;

	$pdf->SetXY(30,$y);
	$pdf->SetFont('Arial','',11);
	$pdf->Cell(30, LH, $label['piezo_vol'], 1, 0, 'C');
	$pdf->Cell(30, LH, $monitor['piezo_vol_min'], 1, 0, 'C');
	$pdf->Cell(30, LH, round($monitor['piezo_vol_avg']), 1, 0, 'C');
	$pdf->Cell(30, LH, $monitor['piezo_vol_max'], 1, 0, 'C');
	list($_lib, $_color) = get_result( $a_result['piezo_vol'] );
	$pdf->CellResult( $label[$_lib], $_color );
	$y += LH;

	$pdf->SetXY(30,$y);
	$pdf->SetFont('Arial','',11);
	$pdf->Cell(30, LH, $label['piezo_freq'], 1, 0, 'C');
	$pdf->Cell(30, LH, $monitor['piezo_freq_min'], 1, 0, 'C');
	$pdf->Cell(30, LH, round($monitor['piezo_freq_avg']), 1, 0, 'C');
	$pdf->Cell(30, LH, $monitor['piezo_freq_max'], 1, 0, 'C');
	list($_lib, $_color) = get_result( $a_result['piezo_freq'] );
	$pdf->CellResult( $label[$_lib], $_color );
	$y += LH;

	$pdf->SetXY(30,$y);
	$pdf->SetFont('Arial','',11);
	$pdf->Cell(30, LH, $label['ctn'], 1, 0, 'C');
	$pdf->Cell(30, LH, $monitor['ctn_min'], 1, 0, 'C');
	$pdf->Cell(30, LH, round($monitor['ctn_avg']), 1, 0, 'C');
	$pdf->Cell(30, LH, $monitor['ctn_max'], 1, 0, 'C');
	list($_lib, $_color) = get_result( $a_result['ctn'] );
	$pdf->CellResult( $label[$_lib], $_color );
	$y += LH;

	$pdf->SetXY(30,$y);
	$pdf->SetFont('Arial','',11);
	$pdf->Cell(30, LH, $label['pump_freq'], 1, 0, 'C');
	$pdf->Cell(30, LH, $monitor['pump_freq_min'], 1, 0, 'C');
	$pdf->Cell(30, LH, round($monitor['pump_freq_avg']), 1, 0, 'C');
	$pdf->Cell(30, LH, $monitor['pump_freq_max'], 1, 0, 'C');
	list($_lib, $_color) = get_result( $a_result['pump_freq'] );
	$pdf->CellResult( $label[$_lib], $_color );
	$y += LH*2;
	
	$pdf->Set_Y($y);
	// <--

	$pdf->AddPage(); 
	$pdf->Set_Y(INIT_Y);

	// Chauffe 2
	list($_lib, $_color) = get_result( $a_result['chauf2'] );
	$pdf->AddLine($label['heating_2'], $label[$_lib], $_color);
	$y = $pdf->Get_Y();

	$pdf->SetXY(30,$y);
	$pdf->SetFont('Arial','',11);
	$pdf->Cell(60, LH, $label['hour'], 1, 0, 'C');
	$pdf->Cell(30, LH, $label['timer'], 1, 0, 'C');
	$pdf->Cell(30, LH, $label['tmax'], 1, 0, 'C');
	$pdf->Cell(30, LH, $label['bilame'], 1, 0, 'C');
	$y += LH;

	$pdf->SetXY(30,$y);
	$pdf->SetFont('Arial','',11);
	$pdf->Cell(60, LH, $result['chauf2_start'], 1, 0, 'C');
	$pdf->Cell(30, LH, $result['chauf2_time'].'s', 1, 0, 'C');
	$pdf->Cell(30, LH, $result['chauf2_Tmax'].'°C', 1, 0, 'C');
	$pdf->Cell(30, LH, $result['chauf2_bilame'].'s', 1, 0, 'C');
	$y += LH*2;
	// <--

	// Graph
	$url_graph = 'http://'.$_SERVER["HTTP_HOST"].substr( $_SERVER["REQUEST_URI"], 0, strrpos( $_SERVER["REQUEST_URI"], "/" )+1 ).'pdf_graph.php?id='.$_GET['id'];
	$img = PATH_PROD_TMP.str_replace('.', '_', $result['serial_machine']).'.png';
	file_put_contents( $img, file_get_contents($url_graph) );
	$pdf->Image($img,10,$y,190);
	unlink($img);
	// <--
	
	// Step fail
	// {"date_1":["etape","code_err"],"date_2":["etape","code_err"],"date_3":["etape","code_err"]}
	$step_fail = json_decode( $result['step_fail'], true );
	if( !empty($step_fail) )
	{
		$y = $pdf->Get_Y();
		$y += 135;
		$pdf->Set_Y($y);
		
		$pdf->AddLine($label['step_fail'].' :');
		
		foreach( $step_fail as $date => $step )
		{
			$pdf->AddLine('  - '.$date.' : ', $label['step_'.$step]);
		}
	}
	// <--
	
	// $pdf->Output();
	$pdf->Output('F', PATH_PROD_PDF.str_replace('.', '_', $result['serial_machine']).'_'.date('Y_m_d', time()).'.pdf');
}
elseif( $req->num_rows == 0 )
{
	echo 'Pas d\'enregistrement id_machine #'.$_GET['id'].'</br>';
	exit;
}
else
{
	$mysqli->print_msg_error();
	exit;
}
?>