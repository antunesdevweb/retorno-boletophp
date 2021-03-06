<?php

namespace ManoelCampos\RetornoBoleto;

require_once("RetornoInterface.php");

/**
 * Classe base para leitura de arquivos de retorno de cobranças dos bancos brasileiros.
 * Sub classes desta classe deve implementar métodos especificos para 
 * o formato de arquivo de retorno que cada uma suporta.
 * 
 * @license <a href="https://opensource.org/licenses/MIT">MIT License</a>
 * @author <a href="http://manoelcampos.com/contact">Manoel Campos da Silva Filho</a>
 * @version 1.1
 * @abstract
 */
abstract class RetornoAbstract implements RetornoInterface {
    /** @property string $nomeArquivo Nome do arquivo de texto a ser lido */
    private $nomeArquivo = "";

    /** 
     * Construtor padrão.
     * 
     * @param string $nomeArquivo Nome do arquivo de retorno do banco.
     */
    public function __construct($nomeArquivo = NULL) {
        $this->setNomeArquivo($nomeArquivo);
    }

    public function setNomeArquivo($nomeArquivo) {
        if (!isset($nomeArquivo) || trim($nomeArquivo) == "") {
            throw new \Exception("O nome do arquivo não pode ser NULL nem vazio.");
        }
        
        $this->nomeArquivo = $nomeArquivo;
    }

    public function getNomeArquivo() {
        return $this->nomeArquivo;
    }
   
    /** 
     * Formata uma string, contendo um valor real (float) sem o separador de decimais,
     * para a sua correta representação real.
     * 
     * @param string $valor String contendo o valor na representação
     * usada nos arquivos de retorno do banco, sem o separador de decimais.
     * @param int $numCasasDecimais Total de casas decimais do número
     * representado em $valor.
     * @return float Retorna o número representado em $valor, no seu formato float,
     * contendo o separador de decimais. 
     */
    protected function formataNumero($valor, $numCasasDecimais = 2) {
        if ($valor == "") {
            return 0;
        }
        
        $casas = $numCasasDecimais;
        if ($casas > 0) {
            $valor = substr($valor, 0, strlen($valor) - $casas) . "." . substr($valor, strlen($valor) - $casas, $casas);
            return (float)$valor;
        }
        
        return (int)$valor;
    }

    /** 
     * Formata uma string, contendo uma data sem o separador, no formato DDMMAA,
     * para o formato DD/MM/AAAA.
     * 
     * @param string $data String contendo a data no formato DDMMAA.
     * @return string Retorna a data non formato DD/MM/AAAA. */
    protected function formataData($data) {
        if ($data == "") {
            return "";
        }
        
        //formata a data par ao padrão americano MM/DD/AA
        $data = substr($data, 2, 2) . "/" . substr($data, 0, 2) . "/" . substr($data, 4, 2);

        //formata a data, a partir do padrão americano, para o padrão DD/MM/AAAA
        return date("d/m/Y", strtotime($data));
    }

    public function arquivoEstaNoFormato($linha){
        return (strlen($linha) >= $this->getTotalCaracteresPorLinha()) &&
               (strlen($linha) <= ($this->getTotalCaracteresPorLinha() + 3));
    }
    
    /** 
     * Processa a linha header do arquivo.
     * @param string $linhaIniciandoComEspaco Linha do header do arquivo processado
     * @return array<mixed> Retorna um vetor associativo contendo os valores do header do arquivo,
     * onde os índices do vetor são os nomes das colunas e o conteúdo de cada posição
     * é o valor da respectiva coluna do header do arquivo.
     */
    protected abstract function processarHeaderArquivo($linhaIniciandoComEspaco);

    /** 
     * Processa um header de lote.
     * @param string $linhaIniciandoComEspaco Linha do header de lote processado
     * @return array<mixed> Retorna um vetor associativo contendo os valores do header de lote,
     * onde os índices do vetor são os nomes das colunas e o conteúdo de cada posição
     * é o valor da respectiva coluna do header de lote.
     */
    protected abstract function processarHeaderLote($linhaIniciandoComEspaco);

    /** 
     * Processa uma linha detalhe do arquivo.
     * @param string $linhaIniciandoComEspaco Linha detalhe do arquivo processado
     * @return array<mixed> Retorna um vetor associativo contendo os valores da linha detalhe lida,
     * onde os índices do vetor são os nomes das colunas e o conteúdo de cada posição
     * é o valor da respectiva coluna da linha detalhe.
     */
    protected abstract function processarDetalhe($linhaIniciandoComEspaco);

    /** 
     * Processa um trailer de lote.
     * @param string $linhaIniciandoComEspaco Linha do trailer de lote processado
     * @return array<mixed> Retorna um vetor associativo contendo os valores do trailer de lote,
     * onde os índices do vetor são os nomes das colunas e o conteúdo de cada posição
     * é o valor da respectiva coluna do trailer de lote.
     */
    protected abstract function processarTrailerLote($linhaIniciandoComEspaco);

    /** 
     * Processa a linha trailer do arquivo.
     * @param string $linhaIniciandoComEspaco Linha trailer do arquivo processado
     * @return array<mixed> Retorna um vetor associativo contendo os valores do trailer do arquivo,
     * onde os índices do vetor são os nomes das colunas e o conteúdo de cada posição
     * é o valor da respectiva coluna do trailer do arquivo.
     */
    protected abstract function processarTrailerArquivo($linhaIniciandoComEspaco);
    
    /**
     * Obtém o tipo da linha passada por parâmetro,
     * se é uma linha de header, detalhe, trailer, etc.
     * @param string $linhaIniciandoComEspaco Linha que deseja verificar qual o tipo.
     * 
     * @see getIdHeaderArquivo()
     * @see getIdHeaderLote()
     * @see getIdDetalhe()
     * @see getIdTrailerLote()
     * @see getIdTrailerArquivo()
     */
    protected abstract function getTipoLinha($linhaIniciandoComEspaco);
    
    public function lerLinhaArquivoRetorno($numLn, $linhaIniciandoComEspaco) {
        $this->validarLinha($numLn, $linhaIniciandoComEspaco);
        
        $objLinha = new LinhaArquivo($numLn, $this->getTipoLinha($linhaIniciandoComEspaco));
        switch ($objLinha->tipo){
            case $this->getIdHeaderArquivo():
                return $objLinha->setDados($this->processarHeaderArquivo($linhaIniciandoComEspaco));
            case $this->getIdHeaderLote():
                return $objLinha->setDados($this->processarHeaderLote($linhaIniciandoComEspaco));
            case $this->getIdDetalhe():
                return $objLinha->setDados($this->processarDetalhe($linhaIniciandoComEspaco));
            case $this->getIdTrailerLote():
                return $objLinha->setDados($this->processarTrailerLote($linhaIniciandoComEspaco));
            case $this->getIdTrailerArquivo():
                return $objLinha->setDados($this->processarTrailerArquivo($linhaIniciandoComEspaco));
        }        
        
        return NULL;
    }
    
    private function validarLinha($numLn, $linha){
        if (trim($linha) == "") {
            throw new \Exception("A linha $numLn está vazia.");
        }
        if (!$this->arquivoEstaNoFormato($linha)) {
            throw new \Exception(
                    "A linha $numLn não tem exatamente " .
                    $this->getTotalCaracteresPorLinha() . 
                    " posições. Possui " . strlen($linha));
        }        
    }
}