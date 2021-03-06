<?php

namespace ManoelCampos\RetornoBoleto;

require_once("RetornoCNAB400Abstract.php");

/**
 * Classe para leitura_arquivos_retorno_cobranças_padrão CNAB400.
 * Layout Padrão CNAB/Febraban 400 posições.<p/>
 * 
 * Baseado na documentação para "Layout de Arquivo Retorno para Convênios
 * na faixa numérica entre 000.001 a 999.999 (Convênios de até 6 posições). 
 * Versão Set/09" do Banco do Brasil (arquivo CBR643-6_posicoes.pdf)
 * 
 * @license <a href="https://opensource.org/licenses/MIT">MIT License</a>
 * @author <a href="http://manoelcampos.com/contact">Manoel Campos da Silva Filho</a>
 * @version 1.1
 */
class RetornoCNAB400 extends RetornoCNAB400Abstract {    
    protected function processarHeaderArquivo($linha) {
        $vetor = array();
        //X = ALFANUMÉRICO 9 = NUMÉRICO V = VÍRGULA DECIMAL ASSUMIDA
        $vetor["registro"] = substr($linha, 1, 1); //9 Identificação do Registro Header: “0”
        $vetor["tipo_operacao"] = substr($linha, 2, 1); //9 Tipo de Operação: “2”
        $vetor["id_tipo_operacao"] = substr($linha, 3, 7); //X Identificação Tipo de Operação “RETORNO”
        $vetor["id_tipo_servico"] = substr($linha, 10, 2); //9 Identificação do Tipo de Serviço: “01”
        $vetor["tipo_servico"] = substr($linha, 12, 8); //X Identificação por Extenso do Tipo de Serviço: “COBRANCA”
        $vetor["complemento1"] = substr($linha, 20, 7); //X Complemento do Registro: “Brancos”
        $vetor["agencia_cedente"] = substr($linha, 27, 4); //9 Prefixo da Agência: N. Agência onde está cadastrado o convênio líder do cedente
        $vetor["dv_agencia_cedente"] = substr($linha, 31, 1); //X Dígito Verificador - D.V. - do Prefixo da Agência
        $vetor["conta_cedente"] = substr($linha, 32, 8); //9 Número da Conta Corrente onde está cadastrado o Convênio Líder do Cedente
        $vetor["dv_conta _cedente"] = substr($linha, 40, 1); //X Dígito Verificador - D.V. - da Conta Corrente do Cedente
        $vetor["convenio"] = substr($linha, 41, 6); //9 Número do convênio líder
        $vetor["nome_cedente"] = substr($linha, 47, 30); //X Nome do Cedente
        $vetor["cod_nome_banco"] = substr($linha, 77, 18); //X 001BANCODOBRASIL
        $vetor["data_gravacao"] = $this->formataData(substr($linha, 95, 6)); //9 Data da Gravação: Informe no formado “DDMMAA”
        $vetor["sequencial_ret"] = substr($linha, 101, 7); //9 Seqüencial do Retorno - nota 01
        $vetor["complemento2"] = substr($linha, 108, 287); //X Complemento do Registro: “Brancos”
        $vetor["sequencial_reg"] = substr($linha, 395, 6); //9 Seqüencial do Registro: ”000001”
        
        return $vetor;
    }

    protected function processarDetalhe($linha) {
        $vetor = array();
        //X = ALFANUMÉRICO 9 = NUMÉRICO V = VÍRGULA DECIMAL ASSUMIDA
        $vetor["registro"] = substr($linha, 1, 1); //9  Identificação do Registro Detalhe: 1 (um)
        //$vetor["zeros1"] = substr($linha, 2, 2); //9  Zeros
        //$vetor["zeros2"] = substr($linha, 4, 14); //9  Zeros
        $vetor["agencia"] = substr($linha, 18, 4); //9  Prefixo da Agência
        $vetor["dv_agencia"] = substr($linha, 22, 1); //X  Dígito Verificador - D.V. - do Prefixo da Agência
        $vetor["cc_cedente"] = substr($linha, 23, 8); //9  Número da Conta Corrente do Cedente
        $vetor["dv_cc_cedente"] = substr($linha, 31, 1); //X  Dígito Verificador - D.V. - do Número da Conta Corrente do Cedente
        $vetor["convenio"] = substr($linha, 32, 6); //9  Número do Convênio de Cobrança do Cedente
        $vetor["controle"] = substr($linha, 38, 25); //X  Número de Controle do Participante
        $vetor["nosso_numero"] = substr($linha, 63, 11); //9  Nosso-Número
        $vetor["dv_nosso_numero"] = substr($linha, 74, 1); //X  DV do Nosso-Número
        $vetor["tipo_cobranca"] = substr($linha, 75, 1); //9  Tipo de cobrança - nota 02
        $vetor["tipo_cobranca_cmd72"] = substr($linha, 76, 1); //9  Tipo de cobrança específico p/ comando 72 
        //   (alteração de tipo de cobrança de títulos das carteiras 11 e 17) - nota 03
        $vetor["dias_calculo"] = substr($linha, 77, 4); //9  Dias para cálculo - nota 04
        $vetor["natureza"] = substr($linha, 81, 2); //9  Natureza do recebimento - nota 05
        $vetor["uso_banco1"] = substr($linha, 83, 3); //X  Uso do Banco
        $vetor["variacao_carteira"] = substr($linha, 86, 3); //9  Variação da Carteira
        $vetor["conta_caucao"] = substr($linha, 89, 1); //9  Conta Caução - nota 06
        $vetor["uso_banco2"] = substr($linha, 90, 5); //9  Uso do Banco
        $vetor["uso_banco3"] = substr($linha, 95, 1); //X  Uso do Banco
        $vetor["taxa_desconto"] = substr($linha, 96, 5); //9  v99 Taxa de desconto
        $vetor["taxa_iof"] = substr($linha, 101, 5); //9  Taxa de IOF
        //$vetor["branco"] = substr($linha, 106, 1); //x  Branco
        $vetor["carteira"] = substr($linha, 107, 2); //9  Carteira
        $vetor["comando"] = substr($linha, 109, 2); //9  Comando - nota 07
        $vetor["data_pagamento"] = $this->formataData(substr($linha, 111, 6)); //X  Data da Entrada/Liquidação (DDMMAA)
        $vetor["num_titulo"] = substr($linha, 117, 10); //X  Número título dado pelo cedente - nota 06
        $vetor["confirmacao"] = substr($linha, 127, 20); //X  Confirmação das posições 63 a 82
        $vetor["data_vencimento"] = substr($linha, 147, 6); //9  Data de vencimento (DDMMAA)
        $vetor["valor_titulo"] = $this->formataNumero(substr($linha, 153, 13)); //9  v99 Valor do título

        $vetor["cod_banco"] = substr($linha, 166, 3); //9  Código do banco recebedor - nota 08
        $vetor["agencia"] = substr($linha, 169, 4); //9  Prefixo da agência recebedora - nota 08
        $vetor["dv_agencia"] = substr($linha, 173, 1); //X  DV prefixo recebedora
        $vetor["especia"] = substr($linha, 174, 2); //9  Espécie do título - 09
        $vetor["data_credito"] = substr($linha, 176, 6); //9  Data do crédito (DDMMAA) - nota 10
        $vetor["valor_tarifa"] = $this->formataNumero(substr($linha, 182, 7)); //9  v99 Valor da tarifa - nota 06
        $vetor["outras_despesas"] = $this->formataNumero(substr($linha, 189, 13)); //9  v99 Outras despesas
        $vetor["juros_desconto"] = $this->formataNumero(substr($linha, 202, 13)); //9  v99 Juros do desconto
        $vetor["iof_desconto"] = $this->formataNumero(substr($linha, 215, 13)); //9  v99 IOF do desconto
        $vetor["valor_abatimento"] = $this->formataNumero(substr($linha, 228, 13)); //9  v99 Valor do abatimento
        //9  v99 Desconto concedido (diferença entre valor do título e valor recebido)
        $vetor["desconto_concedido"] = $this->formataNumero(substr($linha, 241, 13));
        $vetor["valor_pagamento"] = $this->formataNumero(substr($linha, 254, 13)); //9  v99 Valor recebido (valor recebido parcial)
        $vetor["juros_mora"] = $this->formataNumero(substr($linha, 267, 13)); //9  v99 Juros de mora
        $vetor["outros_recebimentos"] = $this->formataNumero(substr($linha, 280, 13)); //9  v99 Outros recebimentos
        $vetor["abatimento_nao_aprov"] = $this->formataNumero(substr($linha, 293, 13)); //9  v99 Abatimento não aproveitado pelo sacado
        $vetor["valor_lancamento"] = $this->formataNumero(substr($linha, 306, 13)); //9  v99 Valor do lançamento
        $vetor["indicativo_dc"] = substr($linha, 319, 1); //9  Indicativo de débito/crédito - nota 11
        $vetor["indicador_valor"] = substr($linha, 320, 1); //9  Indicador de valor - nota 12
        $vetor["valor_ajuste"] = $this->formataNumero(substr($linha, 321, 12)); //9  v99 Valor do ajuste - nota 13
        
        /*
        $vetor["brancos1"] = substr($linha, 333, 1); //X  Brancos (vide observação para cobrança compartilhada) 14
        $vetor["brancos2"] = substr($linha, 334, 9); //9  Brancos (vide observação para cobrança compartilhada) 14
        $vetor["zeros3"] = substr($linha, 343, 6); //9 Zeros - nota 14
        $vetor["zeros4"] = substr($linha, 349, 9); //9 Zeros - nota 14
        $vetor["zeros5"] = substr($linha, 358, 6); //9 Zeros - nota 14
        $vetor["zeros6"] = substr($linha, 364, 9); //9 Zeros - nota 14
        $vetor["zeros7"] = substr($linha, 373, 6); //9 Zeros - nota 14
        $vetor["zeros8"] = substr($linha, 379, 9); //9 Zeros - nota 14
        $vetor["brancos3"] = substr($linha, 388, 5); //X Brancos
        */
        $vetor["canal_pag_titulo"] = substr($linha, 393, 2); //9 Canal de pagamento do título utilizado pelo sacado - nota 15
        $vetor["sequencial"] = substr($linha, 395, 6); //9 Seqüencial do registro

        return $vetor;
    }

    protected function processarTrailerArquivo($linha) {
        $vetor = array();
        //X = ALFANUMÉRICO 9 = NUMÉRICO V = VÍRGULA DECIMAL ASSUMIDA
        $vetor["registro"] = substr($linha, 1, 1); //9  Identificação do Registro Trailer: “9”
        $vetor["2"] = substr($linha, 2, 1); //9  “2”
        $vetor["01"] = substr($linha, 3, 2); //9  “01”
        $vetor["001"] = substr($linha, 5, 3); //9  “001”
        //$vetor["brancos"] = substr($linha, 8, 10); //X  Brancos
        $vetor["cob_simples_qtd_titulos"] = substr($linha, 18, 8); //9  Cobrança Simples - quantidade de títulos
        $vetor["cob_simples_vlr_total"] = $this->formataNumero(substr($linha, 26, 14)); //9  v99 Cobrança Simples - valor total
        $vetor["cob_simples_num_aviso"] = substr($linha, 40, 8); //9  Cobrança Simples - Número do aviso
        $vetor["cob_simples_brancos"] = substr($linha, 48, 10); //X  Cobrança Simples - Brancos
        $vetor["cob_vinc_qtd_titulos"] = substr($linha, 58, 8); //9  Cobrança Vinculada - quantidade de títulos
        $vetor["cob_vinc_valor_total"] = $this->formataNumero(substr($linha, 66, 14)); //9  v99 Cobrança Vinculada - valor total
        $vetor["cob_vinc_num_aviso"] = substr($linha, 80, 8); //9  Cobrança Vinculada - Número do aviso
        $vetor["cob_vinc_brancos"] = substr($linha, 88, 10); //X  Cobrança Vinculada - Brancos
        $vetor["cob_cauc_qtd_titulos"] = substr($linha, 98, 8); //9  Cobrança Caucionada - quantidade de títulos
        $vetor["cob_cauc_vlr_total"] = $this->formataNumero(substr($linha, 106, 14)); //9  v99 Cobrança Caucionada - valor total
        $vetor["cob_cauc_num_aviso"] = substr($linha, 120, 8); //9  Cobrança Caucionada - Número do aviso
        $vetor["cob_cauc_brancos"] = substr($linha, 128, 10); //X  Cobrança Caucionada - Brancos
        $vetor["cob_desc_qtd_titulos"] = substr($linha, 138, 8); //9  Cobrança Descontada - quantidade de títulos
        $vetor["cob_desc_vlr_total"] = $this->formataNumero(substr($linha, 146, 14)); //9  v99 Cobrança Descontada - valor total
        $vetor["cob_desc_num_aviso"] = substr($linha, 160, 8); //9  Cobrança Descontada - Número do aviso
        $vetor["cob_desc_brancos"] = substr($linha, 168, 50); //X  Cobrança Descontada - Brancos
        $vetor["cob_vendor_qtd_titulos"] = substr($linha, 218, 8); //9  Cobrança Vendor - quantidade de títulos
        $vetor["cob_vendor_vlr_total"] = $this->formataNumero(substr($linha, 226, 14)); //9  v99 Cobrança Vendor - valor total
        $vetor["cob_vendor_num_aviso"] = substr($linha, 240, 8); //9  Cobrança Vendor - Número do aviso
        $vetor["cob_vendor_brancos"] = substr($linha, 248, 147); //X  Cobrança Vendor – Brancos
        $vetor["sequencial"] = substr($linha, 395, 6); //9  Seqüencial do registro

        return $vetor;
    }

    public function getIdDetalhe() {
        return 1;
    }

    /**
     * O padrão de arquivo não tem lotes
     * @return int -1 para indicar que não há lotes
     */
    public function getIdHeaderLote() { return -1; }

    /**
     * O padrão de arquivo não tem lotes
     * @return int -1 para indicar que não há lotes
     */
    public function getIdTrailerLote() { return -1; }
    
    /**
     * O padrão de arquivo não tem lotes.
     * @return array<mixed> return um array vazio pois o padrão de arquivo não tem lotes
     */
    protected function processarHeaderLote($linha) { return array(); }

    /**
     * O padrão de arquivo não tem lotes.
     * @return array<mixed> return um array vazio pois o padrão de arquivo não tem lotes
     */
    protected function processarTrailerLote($linha) { return array(); }
}