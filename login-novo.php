<?
if ($_REQUEST['cmd'] != 'logout') $session_start = false;
include_once 'config/config.inc.php';
include_once PATH . 'include/includes.inc.php';

function num2code($n)
{
	
}

class Login extends Cadastro
{
	function num2week($n)
	{
		switch($n){
			case 0: return 'Sun'; Break;
			case 1: return 'Mon'; Break;
			case 2: return 'Tue'; Break;
			case 3: return 'Wed'; Break;
			case 4: return 'Thu'; Break;
			case 5: return 'Fri'; Break;
			case 6: return 'Sat'; Break;
			case 7: return 'Sun'; Break;
		}
	}
	function permIP($data)
	{		
		//p($data);
		$ip = c("SELECT IP_UNIDADE FROM EMPRESA 
					WHERE LICENCIADO = $data[LICENCIADO] AND UNIDADE = $data[UNIDADE]");
		//echo $_SERVER[REMOTE_ADDR] . '<br>';
		if ($_SERVER[REMOTE_ADDR] == $ip or $data[NIVEL] == 1) return true;
		else 
		{
			docopen();
			msgerro("Acesso negado.<br>O seu IP ($_SERVER[REMOTE_ADDR]) nÃ£o estÃ¡ autorizado a efetuar o login.<br>
					<div style='text-align: right'><a href='login.php'>Voltar</a></div>"); 
			die;
		}
	}

	function Enviaremail()
	{
		$email = $this->erros();
		
		
			$cassunto = "Seu nome de usuÃ¡rio e senha no SAE";

			$wmsg = " OlÃ¡ $email[USUARIO]!

O seguinte nome de usuÃ¡rio e senha estÃ¡ incorporado ao seu endereÃ§ccedil;o de e-mail:

UsuÃ¡rio: $email[USUARIO], Senha: $email[SENHA].

NÃ£o forneÃ§ccedil;a sua senha para terceiros, guarde ela com cuidado. Cada usuÃ¡rio deve usar o SAE com sua respectiva senha. 

Um AbraÃ§ccedil;o e Sucesso!
Sua Equipe de Suporte SAE.";
			
			$headers = "MIME-Version: 1.1\r\n";
			$headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";
			$headers .= "From: sae@easycomp.com.br\r\n";
			$headers .= "Return-Path: sae@easycomp.com.br\r\n";
			$headers .= "Sender: sae@easycomp.com.br\r\n";
			$headers .= "Reply-To: sae@easycomp.com.br\r\n";
			$headers .= "X-Priority: 1\r\n";
			$headers .= "X-MSMail-Priority: High\r\n";
			$headers .= "Content-Transfer-Encoding: Quoted-Printable\r\n";
			$ok = 1;
			$a = mail($email[EMAIL],$cassunto,$wmsg,$headers);
			
			msgalert('Email enviado');
			go2('login.php');
		
	}

	function limitarAcesso($info)
	{
		$hora = date('H');
		$minutos = date('i');
		$semana = date('w');
		$data = date('D');
		
		
		$horario = cursor($a="SELECT * FROM USUARIO_CADASTRO_HORARIO 
							WHERE LICENCIADO = $info[LICENCIADO] AND UNIDADE = $info[UNIDADE] AND USUARIO = $info[CODIGO] AND DATA = $semana");
		
				

		if($horario){
			foreach ($horario as $h)
			{
				if($this->num2week($h[DATA])==$data and $h[HORARIO_INICIAL]<=$hora.":".$minutos and $hora.":".$minutos<=$h[HORARIO_FINAL])
				{
					$ok = 1;
					break;
				}
				else $ok = 0;
			
			}

			if ($ok) return 1;
			else 
			{
				docopen();
				echo "<div style='width: 100%; text-aligh: center;'>";
				msginformacao('VocÃª nÃ£o tem permissÃ£o para acessar o sistema neste horÃ¡rio.<br> <a href=?cmd=listar>Clique aqui para voltar</a>');
				echo "</div>";
				die;
			}
		}
	}
	
	function erros(){
		
		if(!$_REQUEST[EMAIL] OR !$_REQUEST[LICENCIADO] OR !$_REQUEST[UNIDADE]){
			msgalert('Ã necessario preencher todos os campos!');
			go2('?cmd=enviarSenha');

		}
		else {
			$info = first("SELECT * FROM USUARIO WHERE LICENCIADO = '$_REQUEST[LICENCIADO]' AND UNIDADE = '$_REQUEST[UNIDADE]' AND EMAIL = '$_REQUEST[EMAIL]' AND STATUS IN (1,3)");
			
			if(!$info[EMAIL]){
				msgalert('Este e-mail Ã© invÃ¡lido. NÃ£o podemos recuperar a senha com ele.');
				go2('?cmd=enviarSenha');
			
			}
			else{
				return $info;
			}
		}
			
	}

	function enviarSenha()
	{
		docopen();
		echo '<table width=400 align=center height=80%><tr><td align=center valign=center>';
			formopen('?cmd=Enviaremail');
				quadroopen('',2,array(bgcolor=>white));
					if ($this->erro) echo '<CENTER><B><FONT COLOR="red" size=1>'.$this->erro.'</FONT></B></CENTER>';

					tabopen();
						echo '<IMG SRC="sae.jpg" BORDER="0" WIDTH=300 HEIGHT=150 ALT="">';
					tabclose();
					tabopen();
						row();
						row('Seu e-mail:',text('EMAIL',array(size=>23)));
						row('NÂ°. Licenciado:',text('LICENCIADO',array(size=>4)).'&nbsp;&nbsp;&nbsp;&nbsp;Unidade:'.text('UNIDADE',array(size=>2)));
						row();
						row('',submit('Enviar e-mail'));
					tabclose();
/*				tabopen();
						$imagem = rand(1,27);
						hidden(IMAGEM_COD,$imagem);
						row('<IMG SRC="senhas/'.$imagem.'.jpg" BORDER="" ALT="" height=50 width=160' );
						row('Digite as letras aqui:'.text('ESPELHO',array(size=>8)));row();							
					tabclose();	
*/
					echo '<hr><FONT SIZE="1" COLOR="#333333">VersÃ£o 5.0<CENTER><FONT SIZE="1" COLOR="#333333">&copy; 2011 Easycomp Tec. Ensino em Comp. Editora Ltda. Todos os direitos reservados.<br>
						Desenvolvido por Consys Tecnologia. FÃ¡brica de software</FONT></CENTER>';
				quadroclose();
			formclose();
		echo '</td></tr></table>';
		docclose();
	}
	
	function reparar()
	{
		set_time_limit(0);
		docopen("Resgate");
		$tabelas = lista("SHOW TABLES");
		$cont = count($tabelas);
		foreach($tabelas as $tab)
		{
			query($e="REPAIR TABLE $tab");
		}
		msgalert("Resgate concluÃ­do!");
		docclose();
		go2('login.php');
	}

	function listar()
	{
		docopen();
		echo '<table width=400 align=center height=80%><tr><td align=center valign=center>';
		formopen('?cmd=check');
		quadroopen('',2,array(bgcolor=>white));
		echo '<CENTER><IMG SRC="sae.jpg" BORDER="0" WIDTH=300 HEIGHT=150 ALT=""></CENTER>';
		$erro = $this->erro;
		if ($this->erro) go2("login2.php?msg=".$erro);
		else go2("login2.php");
		echo '<table align=center><tr><td>';
		tabopen();
			row();
			row('UsuÃ¡rio:',text('USER'));
			row('Senha:',password('PASS'));
			row('',submit('Login'));
		tabclose();
		echo "<center>
		<a href=?cmd=enviarSenha>Esqueci a senha</a><BR>";
		echo '</td></tr></table>';
//		echo 'Resgate'.checkbox(REPARAR,array(NULL),array(1));
		echo '<hr><CENTER><FONT SIZE="1" COLOR="#333333">&copy; 2011 Easycomp Tec Ensino em Comp. <br>Editora Ltda. 
			Todos os direitos reservados.</FONT></CENTER>';
		echo '<TABLE width=100%>
		<TR>
			<TD><FONT SIZE="1" COLOR="#333333">VersÃ£o 1.0<Br>CÃ³pia nÂº 1201185</font></TD>
			<TD align=right><FONT SIZE="1" COLOR="#333333">Easycomp<br>71.823.736/0001-08<br>
			Desenvolvido por - Consys Tecnologia</font></TD>
		</TR>
		</TABLE>';
		echo '';
		quadroclose();
		formclose();
		echo '</td></tr></table><script language=JavaScript>document.form1.USER.focus();</script>';
		docclose();
	}

	function forcar()
	{
		session_start();
		$session = new Session();
		$session->login(true);
		$usuario = first("SELECT USU.*,UNI.*
							FROM USUARIO USU
							LEFT JOIN UNIDADE UNI ON
								UNI.LICENCIADO = USU.LICENCIADO AND
								UNI.COD_SEQ_UNI = USU.UNIDADE
							WHERE USU.CODIGO = '{$_SESSION[USUARIO][CODIGO]}'		
							AND USU.LICENCIADO = $_SESSION[LICENCIADO]
							AND UNIDADE = $_SESSION[UNIDADE]");
		$this->inicializarVerificacoesIniciais($usuario);
	}

	function check()
	{
		
//nao pode deixar criar um usuario que ja existe independente da chave licenciado e unidade				
		$usuario = first("SELECT USU.*,UNI.*
							FROM USUARIO USU
							LEFT JOIN UNIDADE UNI ON
								UNI.LICENCIADO = USU.LICENCIADO AND
								UNI.COD_SEQ_UNI = USU.UNIDADE
							WHERE 
								USU.USUARIO = '$_REQUEST[USER]'		AND 
								USU.SENHA = '$_REQUEST[PASS]'");
		$sucesso = $usuario[LICENCIADO] ? 1 : 0;
		mysql_query("
			INSERT INTO USUARIO_LOG VALUES
			( '', '$_SERVER[REMOTE_ADDR]', '$_REQUEST[USER]', '$_REQUEST[PASS]', SYSDATE(), '$usuario[LICENCIADO]', '$usuario[UNIDADE]', $sucesso )
		");

		if(!$_REQUEST[USER] AND !$_REQUEST[PASS]){
			$this->erro = 'Digite os dados para login.';
			$this->listar();
			die;
		}
		if(!$_REQUEST[USER] AND $_REQUEST[PASS]){
			$this->erro = 'UsuÃ¡rio Ã© obrigatÃ³rio.';
			$this->listar();
			die;
		}
		if(!$_REQUEST[PASS] AND $_REQUEST[USER]){
			$this->erro = 'Senha Ã© obrigatÃ³rio.';
			$this->listar();
			die;
		}

		if ($usuario['CODIGO'] AND $usuario['STATUS']==1 OR $usuario['STATUS']==3)
		{
			session_start();
			
			//if ($_SERVER[REMOTE_ADDR]!='189.30.107.38')
			
			$this->checarDuplicados($usuario);
		
			docopen();
			$_SESSION['UNIDADE'] = $usuario['UNIDADE'];
			$_SESSION[REPRESENTANTE] = $usuario['CODIGO'];
			$_SESSION['LICENCIADO'] = $usuario['LICENCIADO'];	
			$_SESSION['USUARIO']['GRUPO'] = $usuario['GRUPO'];
			$_SESSION['USUARIO']['USER'] = $usuario['USUARIO'];
			$_SESSION['USUARIO']['CODIGO'] = $usuario['CODIGO'];
			$_SESSION['PLANO_CONTAS'] = $usuario['PLANO_CONTAS'];			
			$_SESSION['USUARIO']['CONTATO_ALUNO'] = $usuario['ALUNO'];
			$_SESSION['UND_CENTRAL'] = $usuario['UND_CENTRALIZADORA'];			
			$empresa = first("SELECT * 
							  FROM EMPRESA 
							  WHERE LICENCIADO = '$_SESSION[LICENCIADO]' 
							  AND UNIDADE = '$_SESSION[UNIDADE]'");
			$_SESSION[__TEMPLATE__] = $empresa[TEMPLATE];
			$_SESSION['EMPRESA'] = $empresa['CODIGO'];
			$_SESSION['CARENCIA'] = $empresa['CARENCIA'];
			$_SESSION['EMPRESA_RECIBO'] = $empresa['RECIBO'];
			$_SESSION['EMPRESA_CAIXA'] = $empresa['CAIXA_CENTRAL'];
			$_SESSION['EMPRESA_ACUMULO'] = $empresa['CONTA_ACUMULO'];
			$_SESSION['EMPRESA_COMISSAO'] = $empresa['CARGA_HORARIA'];
			$_SESSION['EMPRESA_CARGA_HORARIA'] = $empresa['CARGA_HORARIA'];
			$_SESSION['EMPRESA_FECHAMENTO'] = $empresa['CONTA_FECHAMENTO'];
			$_SESSION['EMPRESA_CANCELA_TITULO'] = $empresa['CANCELA_TITULO'];
			$_SESSION['EMPRESA_COBRA_REPOSICAO'] = $empresa['COBRA_REPOSICAO'];
			$_SESSION['UTILIZA_BIOMETRIA'] = $empresa['BIOMETRIA'];
			$_SESSION['FICHA_PEDAGOGICA'] = $empresa['FICHA_PEDAGOGICA'];
			$_SESSION['REPLICACAO'] = $empresa['REPLICACAO'];
			$_SESSION['EASYCOMP_USER'] = $empresa['EASYCOMP_USER'];
			$_SESSION['EASYCOMP_SENHA'] = $empresa['EASYCOMP_SENHA'];
			$_SESSION['CONTROLE_ACESSO'] = $empresa['CONTROLE_ACESSO'];
			$_SESSION['BLOQUEIO_MATRICULA'] = $empresa['BLOQUEIO_MATRICULA'];
			
			
							
			if ($empresa[CONTROLE_ACESSO] == 1)
			{	
				//CONTROLE DE IP
				$this->permIP($usuario);
			}
			
			if ($empresa[CONTROLE_ACESSO] == 2 )
			{
				//CONTROLE DE HORARIO
				if($_SESSION[USUARIO][USER]!="$_SESSION[UNIDADE]$_SESSION[LICENCIADO]")
					if($usuario[NIVEL]<>1) $this->limitarAcesso($usuario);
			}
			
	
			if($empresa[MIGRACAO]==1) $_SESSION[MIGRACAO]=1;
			if($_REQUEST[REPARAR]){
				go2('?cmd=reparar');
			}

			if($usuario[LICENCIADO] == 506)
				$_SESSION['CONTA_DIFERENCA506'] = $empresa['CONTA_DIFERENCA'];


			if ($usuario['PESSOA'])
				list($_SESSION['USUARIO']['NOME']) = linha("SELECT NOME FROM PESSOA WHERE CODIGO = $usuario[PESSOA]");

			$session = new Session();
			if (!$session->login()) {
				echo $session->erro("<br><div align=right><a href=?cmd=forcar>ForÃ§ar entrada</a></div>");
				die;
			}

		/*	if($usuario[ALUNO]>0 and $empresa[REPLICACAO]==1){
				$tabelas="ALUNO,MATRICULA,MATRICULA_DISCIPLINA,MATRICULA_FICHA,USUARIO,GRADE_FIXA,GRADE_PEDAGOGICA,TITULO";
				go2("module/migracao/replicacao.php?cmd=carregar&ORIGEM=REMOTO&DESTINO=UNIDADE&TABELAS=$tabelas&ALUNO=$usuario[ALUNO]");
				die;
			}else {*/
				$this->inicializarVerificacoesIniciais($usuario);
			//}
			docclose();
			
		}
		elseif($usuario['CODIGO'] AND $usuario['STATUS']==2)
		{
			//p($usuario['CODIGO']);
			$this->erro = 'UsuÃ¡rio inativo.';
			$this->listar();
		}
		else
		{
			$this->erro = 'NÃ£o autenticado.';
			$this->listar();
		}
	}

	function checarDuplicados($usuario)
	{
		$qt = c($a="SELECT COUNT(1) FROM USUARIO WHERE USUARIO = '$usuario[USUARIO]'");
		if($qt>1) {
			echo "<form action=novo_usuario.php method=post>";
			hidden(USUARIO, $usuario[USUARIO]);
			hidden(SENHA, $usuario[SENHA]);
			hidden(LICENCIADO, $usuario[LICENCIADO]);
			hidden(UNIDADE, $usuario[UNIDADE]);
			echo "</form>";
			
			echo "<script type=text/javascript> document.forms[0].submit() </script>";
			die;
		}
		else return 0;
	}
	
	function inicializarVerificacoesIniciais($usuario)
	{

		
		include_once PATH.'setup/setup.php';
	
		$this->tabela = Tabela::factory(TITULO);
		$this->tabela->AcertaFollowChamado();
		$this->tabela->CalculoBeneficio();
		$this->tabela->atualizartituloslicenciado();	
		if($usuario[ALUNO] AND $usuario[ALUNO] > 0){
			$this->tabela = Tabela::factory(MATRICULA_FICHA);
			$this->tabela->marcarPresenca($usuario);
		}
		$perm = first("SELECT * FROM XW_PERM WHERE USUARIO_GRUPO = '$usuario[CODIGO]' AND ITEM = '588' ");

		if ((($perm  or $usuario[NIVEL] == 1) and $_SESSION[CONTROLE_ACESSO] == 1) or ($_SESSION[LICENCIADO] == 506 and $usuario[NIVEL] == 1)) go2('ip506.php');

		go2('index.php');
	}

	function logout()
	{
		execute("DROP TABLE IF EXISTS `TITULO_COBREBEM_17851`");
		SESSION::logoff();
		session_unset();
		session_destroy();
		$this->listar();
	}
}

$int = new Login();
$int -> run();

?>
<?
if ($_REQUEST['cmd'] != 'logout') $session_start = false;
include_once 'config/config.inc.php';
include_once PATH . 'include/includes.inc.php';

