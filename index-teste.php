<?
include_once 'config/config2.inc.php';
include_once PATH_XWEB . 'componente/calendario.class.php';
ini_set ( "memory_limit", "600M" );
//include_once PATH_XWEB.'componente/grafico.class.php';
if(array_key_exists(CLIENTE,$_SESSION[USUARIO])){//veio do painel da easycomp e precisa refazer a session
	go2('login.php?cmd=logout');
}

class CalendarioAgendamento extends Calendario 
{
    var $showCalendario = true; 

    var $cores_status = array(
        '' =>  '#fffecb',
        '1' => '#AED5EA',
        '2' => '#FF7979',
        '3' => '#99ff99',
        '4' => '#E4E4E4',
    );
	
	function pesquisa()
	{
		$pesquisa = first("SELECT * FROM PESQUISA 
						   WHERE LICENCIADO = '$_SESSION[LICENCIADO]' AND
								 UNIDADE = '$_SESSION[UNIDADE]'		  AND
								 DT_INICIO <= CURDATE()				  AND 
								 STATUS = 1	AND FUNC = 1			  AND
								 (DT_FIM IS NULL		OR 
								  DT_FIM = '0000-00-00' OR
								  DT_FIM >= CURDATE())
							ORDER BY DT_INICIO");
		if(!$pesquisa) $abrepesquisa=0;
		else{
			$perguntas = c("SELECT MAX(PERGUNTA) FROM PESQUISA_PERGUNTA
							WHERE LICENCIADO = '$_SESSION[LICENCIADO]' AND
								  UNIDADE = '$_SESSION[UNIDADE]'	   AND
								  PESQUISA = '$pesquisa[PESQUISA]'");
			$perguntas_resp = c("SELECT MAX(PERGUNTA) FROM PESQUISA_RESPOSTA
							     WHERE LICENCIADO = '$_SESSION[LICENCIADO]' AND
									   UNIDADE = '$_SESSION[UNIDADE]'	    AND
									   PESQUISA = '$pesquisa[PESQUISA]'     AND
									   USUARIO = '{$_SESSION[USUARIO][CODIGO]}'");
			if($perguntas==$perguntas_resp) $abrepesquisa=0;
			else{
				$registro = first("SELECT * FROM PESQUISA_REGISTRO
								   WHERE LICENCIADO = '$_SESSION[LICENCIADO]' AND
										 UNIDADE = '$_SESSION[UNIDADE]'	      AND
										 PESQUISA = '$pesquisa[PESQUISA]'	  AND
									     USUARIO = '{$_SESSION[USUARIO][CODIGO]}'");
				if(!$registro or (!$registro[CONCLUSAO] AND !$registro[IGNORADO])) $abrepesquisa=1;
				
			}
		}
		if($abrepesquisa==1){
			echo
				"<script>
					window.open('module/pesquisa/responder.php?cmd=listar&PESQUISA=$pesquisa[PESQUISA]','','menubar=yes,scrollbars=yes,,height=500,width=700');      
				</script>";
		}
	}

    function getTimeCardsDia($dia,$subnivel)
    {
        $dados = cursor($sql = "
            SELECT
                CONCAT('C/',USUARIO_DESTINO,'/',CODIGO) CODIGO,
                CONCAT(ASSUNTO,' ',IF(DATA_CONCLUSAO IS NOT NULL,' (Conclusão: ',''),IFNULL(DATE_FORMAT(DATA_CONCLUSAO,'%d/%m/%Y'),''),IF(DATA_CONCLUSAO IS NOT NULL,', ',''),IFNULL(TIME_FORMAT(HORA_CONCLUSAO,'%H:%i'),''),IF(DATA_CONCLUSAO IS NOT NULL,')','')) DESCRICAO,
                DATE_FORMAT(CONCAT(DATA_AGENDAMENTO,' ',HORA_AGENDAMENTO),'%d/%m/%Y %H:%i') DT_HR_INICIAL,
                DATE_FORMAT(CONCAT(DATA_AGENDAMENTO,' ',HORA_AGENDAMENTO) + INTERVAL 30 MINUTE,'%d/%m/%Y %H:%i') DT_HR_FINAL,
                IF(STATUS = 1 AND USUARIO_CADASTRO IS NOT NULL,'3',IF(PRIORIDADE = 1 AND USUARIO_CADASTRO IS NOT NULL,'2','')) STATUS
            FROM CHAMADO
            WHERE LICENCIADO = '$_SESSION[LICENCIADO]' AND
                UNIDADE = '$_SESSION[UNIDADE]' AND
                DATA_AGENDAMENTO = '".date('Y-m-d',$dia)."' AND
                ((USUARIO_CADASTRO = '{$_SESSION[USUARIO][CODIGO]}' AND STATUS = 1) OR 
                (USUARIO_DESTINO = '{$_SESSION[USUARIO][CODIGO]}' AND STATUS != 1))
            
            UNION
                        
            SELECT 
                CONCAT('F/',ALF.ALUNO,'/',ALF.NROSEQ,'/',ALF.COMENTARIO) CODIGO,
                CONCAT(' ',ALU.NOME) DESCRICAO,
                DATE_FORMAT(
                    CONCAT(DATA_AGENDAMENTO, ' ',IFNULL(HORA_AGENDAMENTO,'08:00')),
                    '%d/%m/%Y %H:%i:%s') DT_HR_INICIAL,
                DATE_FORMAT(
                    CONCAT(DATA_AGENDAMENTO, ' ',IFNULL(HORA_AGENDAMENTO,'08:00:00'))+INTERVAL 30 MINUTE,
                    '%d/%m/%Y %H:%i:%s')  DT_HR_FINAL,
                IF(SITUACAO=2,'4','1') STATUS
            FROM ALUNOS_FOLLOWUP ALF
            LEFT JOIN ALUNO ALU ON (
                ALU.LICENCIADO = ALF.LICENCIADO AND
                ALU.UNIDADE = ALF.UNIDADE AND
                ALU.PROSPECTS = ALF.ALUNO
                )
            WHERE ALF.LICENCIADO = '$_SESSION[LICENCIADO]' AND
                ALF.UNIDADE = '$_SESSION[UNIDADE]' AND
                DATA_AGENDAMENTO = '".date('Y-m-d',$dia)."' AND
                USUARIO_DESTINO = '{$_SESSION[USUARIO][CODIGO]}'");
        return $dados;    

    }

    function getDescricaoTimeCard($timecard)
    {
        return "$timecard[HORA] - $timecard[DESCRICAO]";
    }

    function edit()
    {    
        $_REQUEST[DATA] = substr($_REQUEST[DATA],1);
        if ($_REQUEST[CODIGO]) {
            list($tipo,$contato,$codigo,$comentario) = explode('/',$_REQUEST[CODIGO]);
            if ($tipo == 'C') # chamado
                go2(URL."module/cadastro/chamado.php?cmd=updateView&LICENCIADO=$_SESSION[LICENCIADO]&UNIDADE=$_SESSION[UNIDADE]&CODIGO=$codigo");
            else{
                go2(URL."module/cadastro/follow_up.php?cmd=insertView&LICENCIADO=$_SESSION[LICENCIADO]&UNIDADE=$_SESSION[UNIDADE]&ALUNO=$contato&NROSEQ=$codigo&COMENTARIO=$comentario&DATA=$_REQUEST[DATA]&VOLTAR=INDEX");
            }
        } else go2(URL."module/cadastro/chamado.php?cmd=insertView&DATA_AGENDAMENTO=$_REQUEST[DATA]&HORA_AGENDAMENTO=$_REQUEST[HORA]");
    }
    function listar()
    {

		$this->checkFicha();
		$this->pesquisa();
        $menu = new Menu();
//        $menu->showMenus();

            docopen('');
            $this->show();
    
    }
	
	function graficArray($serie,$x,$y,$_GRAFICO)
	{
		switch ($_GRAFICO->Agruparordenada) {
			case "Y" : $agrupar = 'ano'; break;
			case "m" : $agrupar = 'mes'; break;
			case "d" : $agrupar = 'dia'; break;
		}
		if($agrupar){
			list($dia,$mes,$ano) = explode('/',$x);
			if (!checkdate($mes,$dia,$ano)){ echo "nao ta correta a data"; die;}

			$ordenada =  $_GRAFICO->serie[$serie][$$agrupar][0] ? $_GRAFICO->serie[$serie][$$agrupar][1] + $y : $y;
			if ($_GRAFICO->serie[$serie][$$agrupar][0]){
				$_GRAFICO->serie[$serie][$$agrupar][1] += $y;
			}
			else{
				$_GRAFICO->serie[$serie][$$agrupar][1] = $y;
				$_GRAFICO->serie[$serie][$$agrupar][0] = $x;
			}
			$_GRAFICO->serie[$serie][$$agrupar] = array($$agrupar,$ordenada);
		}
		else{
			$ordenada = $_GRAFICO->serie[$serie][$x][0] ? $_GRAFICO->serie[$serie][$x][1] + $y : $y;
			$_GRAFICO->serie[$serie][$x] = array($x,$ordenada);
		}
		ksort($_GRAFICO->serie[$serie]);
		return $_GRAFICO;
	}

	function checkFicha()
	{
		if ($_SESSION[USUARIO][CONTATO_ALUNO] != 0)
		{
            $aluno = $_SESSION[USUARIO][CONTATO_ALUNO]; 
            $matricula = first($aa="SELECT    
                                        ALUNO.*, 
                                        MATRICULA.*     
                                    FROM ALUNO ALUNO 
                                    LEFT JOIN MATRICULA MATRICULA ON 
                                        MATRICULA.LICENCIADO = ALUNO.LICENCIADO    AND 
                                        MATRICULA.UNIDADE = ALUNO.UNIDADE        AND
                                        MATRICULA.CONTATO = ALUNO.PROSPECTS        AND 
                                        MATRICULA.STATUS = 1     
                                    WHERE 
                                        ALUNO.PROSPECTS = '$aluno' AND     
                                        ALUNO.LICENCIADO ='$_SESSION[LICENCIADO]'     AND
                                        ALUNO.UNIDADE ='$_SESSION[UNIDADE]'");
            go2("module/cadastro/ficha_pedagogica.php?&MATRICULA=$matricula[CODIGO]&CODIGO=$matricula[PROSPECTS]&PROVISORIO=1");
		}
	}

	function afterCalendario()
    {
		formopen('','','','name=template');
		echo "<b><font size = 2>Estilo: </font></b>".listbox('TEMPLATE',
						array('Windows2000','Easycomp','Azul','green','red','mint-choc','brown','white'),
						array('Windows2000','Easycomp','Azul','green','red','mint-choc','brown','white'),
						array("value='$_SESSION[__TEMPLATE__]' id=template"));
		formclose();
		quadroopen('Acesso rápido','249');								 
				row(button(img('add','Prospects'),"redirect('module/cadastro/prospect2.php?cmd=telaAdicionar')",
								'text-align:left; style="height: 22;width:111"').'&nbsp;&nbsp;'.
					button(img('add','Matrículas'),"redirect('module/cadastro/matricula2.php?cmd=telaAdicionar&ORIGEM=lista_geral')",
								'style="text-align:left; height: 22;width:111"'));
				echo "<br style='line-height: 1px;'>";
				row(button(img('money','Meu Caixa'),"redirect('module/financeiro/caixa.php')",
								'style="text-align:left; height: 22;width:111"').'&nbsp;&nbsp;'.
					button(img('find','Horários'),"redirect('module/grade/index_pedagogica.php?cmd=listar&SHOW=work')",
								'style="text-align:left; height: 22;width:111"'));
				echo "<br style='line-height: 1px;'>";
				row(button(img('money','Receber'),"redirect('module/registro/titulo_recebimento_redirect.php')",
								'style="text-align:left; height: 22;width:111"').'&nbsp;&nbsp;'.
					button(img('find','Análise'),"redirect('module/relatorio/caixa301.php')",
								'style="text-align:left; height: 22;width:111"'));
		quadroclose();
		echo "<br style='line-height: 7px;'>";

//		quadroopen('','250','254');
		quadroopen('','239');
			//if ($_SESSION["USUARIO"][USER] == "$_SESSION[UNIDADE]$_SESSION[LICENCIADO]") 
			{
				echo "<center>". button('Passo a passo', '', 'style="height: 30; width:auto" id=passoapasso') . "</center>";
				echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
					button('<font color=#009900 size=1px><b>Gráficos</b>',"redirect('grafico/nivel1.php?cmd=listaGraficos')",'style="height: 25;width:70"').
					button('Ajuda','','style="height: 22;width:63" id=ajuda').
					//button('Idéias','','disabled style="height: 22;width:57" ');
					button('Suporte','',' style="height: 22;width:63" id=Easycomp');
				echo "<div id=lateral>";
				echo "</div>";
				$this->passoapassoModalJs();
			}
			/*else{
				echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
					button('<font color=#9A9A9A size=1px><b>Gráficos</b>',"redirect('grafico/nivel1.php?cmd=listaGraficos')",'style="height: 25;width:70 " disabled').
					button('Ajuda','','style="height: 22;width:63" id=ajuda').
					//button('Idéias','','disabled style="height: 22;width:57" ');
					button('Suporte','',' style="height: 22;width:63" id=Easycomp');
				echo "<div id=lateral>";
				echo "</div>";
			}*/
			
		quadroclose();
		?><SCRIPT LANGUAGE="JavaScript">
			$('#metas').click(function(){
				$("#lateral").load("?cmd=metas");
			})
			$('#ajuda').click(function(){
				$("#lateral").load("?cmd=ajuda");
			})
			$('#Easycomp').click(function(){
				$("#lateral").load("?cmd=easycomp");
			})
			$('#template').change(function(){
				$("#lateral").load("?cmd=trocatemplate&"+$( "form[name=template]" ).serialize());
			})
		</SCRIPT><?
	}
	
	function passoapassoModalJs()
	{
		echo "<script type=text/javascript>
			$(\"#passoapasso\").click(function() {
				var div = $(\"#divNovaTela\");
				if (!div.length) {
					div = $(\"<div id=divNovaTela><iframe style='width: 100%; height: 100%;' id=ifrmNovaTela></iframe></div>\");
					$(document).append(div);
				}
				div.dialog({ 
					width: 800,
					height: 600,
					title: 'Migração:',
					modal: true
				});
				refreshIframe(\"ifrmNovaTela\",\"module/help/passoapasso.php\");
			}); </script>";
	}
	
	function trocatemplate()
	{
		$_SESSION[__TEMPLATE__] = $_REQUEST[TEMPLATE];
		execute("UPDATE EMPRESA SET TEMPLATE = '$_REQUEST[TEMPLATE]' WHERE LICENCIADO = '$_SESSION[LICENCIADO]' AND UNIDADE = '$_SESSION[UNIDADE]' AND CODIGO = '$_SESSION[EMPRESA]'");
		go2('index.php');
	}
		
	function easycomp()
	{
		echo "<br style='line-height: 6px;'>";
		msggreen("	<span style='font-size: 11px; font-weight: normal;' >
					<a href='/module/cadastro/chat.php?cmd=entrar' style='color: blue;' target='_blank'>Chat online</a><br/>
					Email: suporte@sistemasae.com.br<br/>
					Telefone: (11) 2122-5252, op&ccedil;&atilde;o 4<br/> </span>");
	}

	function metas()
	{

//		$grafico = new GraficoPizza();
//		$grafico->largura = 100;
//		$grafico->border = false;
//		$grafico->legenda = right;
		//for ($i = 0; $i < count($totais); $i++) {
//			$grafico->dados['Teste1'] = 20;
//			$grafico->dados['Teste2'] = 30;
//			$grafico->dados['Teste3'] = 50;
			//row($totais[$i][EMPRESA],
			//	round(100*$totais[$i][DURACAO]/$total).'%',
			//	date('H:i',mktime(0,$totais[$i][DURACAO])),
			//array(align=>array(left,right,right)));
		//}
//		$grafico->show();
	}
	
	function ajuda()
	{
		echo "<br style='line-height: 6px;'>";
		msggreen(aimg(URL.'module/help/Iniciantes.php','add','Assitir vídeo-aulas.',"title=' Ajuda gravada' target=blank"));
	}

    function showMenuCabecalho()    
	{
		if(!$_SESSION[PLANO_CONTAS]) $disabled = 'disabled';
		tabopen();tabclose();
		echo button('<font color=#104002 size=1px>Agendar follow-up',"redirect('module/cadastro/telemarketing2.php?cmd=inicio')",'style="height: 22;width:130"');
		echo "&nbsp;";
		echo button('<font color=#104002 size=1px>C.I',"redirect('module/cadastro/chamado.php?cmd=insertView')",'style="height: 22;width:50"');
		echo "&nbsp;";
		echo button('<font color=#104002 size=1px>C.E</font>',"redirect('module/cadastro/email_envio.php?cmd=deletando')",'style="height: 22;width:50;"');
    }
	function Inadimplente()
	{
		$ina = first("SELECT INDICE, DATA FROM INDICE_INADIMPLENCIA WHERE EMPRESA_ID = $_SESSION[EMPRESA] ORDER BY DATA DESC");
		$data = explode(" ", $ina['DATA']);
		
		if($ina['INDICE'] >= 12) $img = 'happy4.gif';
		if($ina['INDICE'] >= 8 and $ina['INDICE'] < 12) $img = 'happy3.gif';
		if($ina['INDICE'] >= 5 and $ina['INDICE'] < 8) $img = 'happy2.gif';
		if($ina['INDICE'] >= 0 and $ina['INDICE'] < 5) $img = 'happy1.gif';
		
		$this->titulo_cabecalho = "<span title='Índice gerado em $data[0] às $data[1] horas'><IMG SRC=icons2/$img BORDER=0 width=18 > Olá ".$_SESSION[USUARIO][USER]."! De todos os alunos ativos pagantes ".aimg("javaScript: atualizar();",'refresh','',"ALT='Atualizar'")." $ina[INDICE]% estão com títulos atrasados!</span>";
		
		
	}
	
}

$int = new CalendarioAgendamento();
$int->titulo = 'Agenda de '.date('Y');
$int->filtro = array(CLIENTE,CASO,STATUS);
echo "<div id='inad'>";
	$int->Inadimplente();	
echo "</div>";
?><script language="JavaScript">
	function atualizar()
	{		
		refreshDiv('inad',"?cmd=Inadimplente&REFRESH=1");
		
	}
</script><?

$int->run();
?>

