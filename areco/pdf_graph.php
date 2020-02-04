<?php 
require_once("_realpath.php");
require_once($path."config.php");
require_once($path."/lib/jpgraph/jpgraph.php");
require_once($path."/lib/jpgraph/jpgraph_date.php");
require_once($path."/lib/jpgraph/jpgraph_line.php");

$mysqli = new My_SQL();

if( !isset($_GET['id']) || !is_numeric($_GET['id']) )
{
	echo 'Error : argument ID is not valid in script pdf_graph.php';
	exit;
}

$sql = 'SELECT * FROM btp_chauf2 WHERE id_machine = '.$_GET['id'].' ORDER BY date ASC';
if( $req = $mysqli->my_query($sql) )
{
	$data_time = array();
	$data_ctn = array();
	$data_task_chauf = array();
	$data_active_thermo = array();
	$data_cur_thermo = array();

	while( $data = $req->fetch_object() )
	{	
		$tmp = DateTime::createFromFormat( 'Y-m-d H:i:s', $data->date );
		$data_time[] .= $tmp->getTimestamp();
		$data_task_chauf[] .= $data->task_chauf*100;
		// $data_active_thermo[] .= $data->active_thermo*100;
		$data_cur_thermo[] .= ceil($data->cur_thermo/10);
		$data_ctn[] .= $data->ctn;
	}

	// Create the graph
	$graph = new Graph(1000,600);
	$graph->SetScale('datint', 0, 105);

	// Title
	// $graph->title->Set($title);
	// $graph->title->SetFont(FF_VERDANA,FS_BOLD,12);
	// $graph->title->SetColor('#e7e6e6');

	// Color
	$graph->SetBox(false);
	$graph->SetMargin(30,20,0,25);
	$graph->SetColor("#FFFFFF");
	$graph->SetMarginColor("#FFFFFF");
	$graph->SetFrame(true,'#FFFFFF', 0); //3c3c3c
	$graph->SetTickDensity( TICKD_DENSE );

	// Anti Aliasing turn off
	$graph->img->SetAntiAliasing(false);

	// X-axis format
	$graph->xaxis->SetColor("#000000");
	$graph->xaxis->scale->SetTimeAlign( MINADJ_5 );
	$graph->xaxis->scale->SetDateFormat('H:i');
	$graph->xaxis->HideTicks(false,false);

	// Y-axis format
	$graph->yaxis->SetColor("#000000");
	$graph->yaxis->SetTickPositions(array(0,10,20,30,40,50,60,70,80,90,100), array(5,15,25,35,45,55,65,75,85,95));
	$graph->yaxis->HideLine(false);
	$graph->yaxis->HideTicks(false,false);
	$graph->ygrid->SetFill(true,'#FFFFFF@0.5','#FFFFFF@0.5'); 
	$graph->ygrid->SetColor("#e7e6e6");

	// Create the linear plot
	$line_task_chauf = new LinePlot( $data_task_chauf, $data_time );
	// $line_active_thermo = new LinePlot( $data_active_thermo, $data_time );
	$line_cur_thermo = new LinePlot( $data_cur_thermo, $data_time );
	$line_ctn = new LinePlot( $data_ctn, $data_time );

	// Add the plot to the graph
	$graph->Add($line_task_chauf);
	// $graph->Add($line_active_thermo);
	$graph->Add($line_cur_thermo);
	$graph->Add($line_ctn);

	// Couleur
	$line_task_chauf->SetColor("#FFE8E8");
	$line_task_chauf->SetFillColor('#FFE8E8');
	// $line_active_thermo->SetColor("#ed7d31");
	$line_cur_thermo->SetColor("#ffc000");
	$line_ctn->SetColor("#FF0000");

	// $line_dc->mark->SetType(MARK_X);
	// $line_dc->mark->SetColor('#e7e6e6');
	// $line_dc->mark->SetFillColor('red');

	// Épaisseur
	$line_task_chauf->SetWeight(1);
	// $line_active_thermo->SetWeight(2);
	$line_cur_thermo->SetWeight(2);
	$line_ctn->SetWeight(3);

	// Legend
	$graph->legend->SetAbsPos(10,10,'right','top');
	$graph->legend->SetLayout(LEGEND_HOR);
	$graph->legend->SetFillColor('#FFFFFF');
	$graph->legend->SetColor('#000000');

	$line_task_chauf->SetLegend("Task_Chauffe");
	// $line_active_thermo->SetLegend("Thermo");
	$line_cur_thermo->SetLegend("Cur_Thermo");
	$line_ctn->SetLegend("T°");

	// Display the graph
	$graph->graph_theme=null;
	$graph->Stroke();
}
else
{
	$mysqli->print_msg_error();
	exit;
}
?>