import re
from datetime import datetime

def _to_float(valor_str: str) -> float | None:
    """Converte uma string de valor monetário para float."""
    if not valor_str:
        return None
    try:
        valor_limpo = valor_str.strip()
        # Se ',' está na string, é o separador decimal
        if ',' in valor_limpo:
            valor_limpo = valor_limpo.replace('.', '').replace(',', '.')
        # Se não tem ',', mas tem '.', o último '.' é o separador decimal
        elif '.' in valor_limpo:
            if valor_limpo.count('.') > 1:
                valor_limpo = valor_limpo.replace('.', '', valor_limpo.count('.') - 1)
        return float(valor_limpo)
    except (ValueError, TypeError):
        return None

def _to_iso_date(data_str: str) -> str | None:
    """Converte uma string de data (ex: '25/09/2025') para o formato ISO (YYYY-MM-DD)."""
    if not data_str:
        return None
    
    # Remove espaços em branco que o OCR possa ter introduzido
    data_str_limpa = data_str.replace(' ', '')

    # Tenta formatos comuns de data
    for fmt in ('%d/%m/%Y', '%d/%m/%y', '%d.%m.%Y'):
        try:
            return datetime.strptime(data_str_limpa, fmt).strftime('%Y-%m-%d')
        except ValueError:
            pass
    return None

def _find_first_match(text: str, patterns: list[str]) -> str | None:
    """Encontra a primeira correspondência para uma lista de padrões regex."""
    for pattern in patterns:
        match = re.search(pattern, text, re.IGNORECASE | re.DOTALL)
        if match and match.group(1):
            return match.group(1).strip()
    return None

# Definição dos padrões de extração para cada categoria
EXTRACTION_RULES = {
    'agua': {
        'valor': lambda text: _to_float(_find_first_match(text, [
            r"Total\s+a\s+Pagar[:\s\n]*?R?\$\s*([\d.,]+)", # Novo: Mais flexível, aceita quebra de linha
            r"TOTAL\s+A\s+PAGAR[:\s]*?R?\$\s*([\d.,]+)", # Padrão ajustado para aceitar ':'
            r"VALOR\s+A\s+PAGAR[:\s]*?R?\$\s*([\d.,]+)", # Padrão ajustado para aceitar ':'
            r"TOTAL\s+A\s+PAGAR[:;]?\s*R?\s*([\d.,]+)",
            r"Valor\s+Total[:;]?\s*R?\s*([\d.,]+)",
            r"TOTAL\s+DA\s+CONTA[:;]?\s*R?\s*([\d.,]+)"
        ])),
        'data_vencimento': lambda text: _to_iso_date(_find_first_match(text, [
            r"Vencimento[:\s\n]*?(\d{2}/\d{2}/\d{4})", # Novo: Mais flexível, aceita quebra de linha
            r"Vencimento[^\d/]*?(\d{2}/\d{2}/\d{4})", # Formato SAAE novo, mais flexível
            r"Data\s+d[eo]\s+Vencimento[:;]?\s*(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})",
            r"VENCIMENTO[^\d/]*?(\d{2}/\d{2}/\d{4})", # Formato SAAE novo, mais flexível
            r"VENCIMENTO[:;]?\s*(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})",
            r"Vence\s+em[:;]?\s*(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})"
        ])),
        'data_emissao': lambda text: _to_iso_date(_find_first_match(text, [
            r"Data\s+de\s+Emiss.o[^\d/]*?(\d{2}/\d{2}/\d{4})", # Formato SAAE novo, mais flexível
            r"Data\s+d[ae]\s+Emissão[:;]?\s*(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})",
            r"EMISSÃO(?:\s|\n)*(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})",
            r"Data\s+do\s+Documento\s+(\d{2}[\s./-]\d{2}[\s./-]\d{4})"
        ])),
        'consumo': lambda text: _to_float(_find_first_match(text, [
            r"Consumo\s+M.s\s+de\s+Refer.ncia[^\d]*?(\d+)", # Formato SAAE novo, mais flexível
            r'Consumo[\s\n]+m³[\s\n]+([\d,.]+)',
            r'Consumo\s+Mês\s+de\s+Referência\s+(\d+)' # Formato SAAE novo com acento
        ]))
    },
    'energia': {
        'pn': lambda text: _find_first_match(text, [r"PN\s*.*?(\d+)"]),
        'seu_codigo': lambda text: _find_first_match(text, [r"SEU CÓDIGO\s*(\d+)"]),
        'conta_mes': lambda text: _find_first_match(text, [r"CONTA MÊS\s*([\d/]+)", r"([A-Z]{3}/\d{4})"]),
        'data_vencimento': lambda text: _to_iso_date(_find_first_match(text, [
            r"[A-Z]{3}/\d{4}\s*(\d{2}[\s./-]\d{2}[\s./-]\d{4})", # Padrão para 'MÊS/ANO DATA'
            r"Vencimento[:;]?\s*(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})",
            r"DATA\s+DE\s+VENCIMENTO[:;]?\s*(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})",
            r"Data\s+d[oa]\s+Vencimento[:;]?\s*(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})",
            r"\b(\d{2}[\s./-]\d{2}[\s./-]\d{4})\b" # Padrão genérico como último recurso
        ])),
        'data_emissao': lambda text: _to_iso_date(_find_first_match(text, [
            r"Data\s+d[ae]\s+Emissão[:;]?\s*(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})",
            r"EMISSÃO(?:\s|\n)*(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})",
            r"Data\s+do\s+Documento\s+(\d{2}[\s./-]\d{2}[\s./-]\d{4})"
        ])),
        'valor': lambda text: _to_float(_find_first_match(text, [
            r"TOTAL\s+A\s+PAGAR[:;]?\s*R?\s*([\d.,]+)",
            r"Valor.*?Pagar[:;]?\s*R?\s*([\d.,]+)",
            r"Total Consolidado[:;]?\s*R?\s*([\d.,]+)"
        ])),
        'total_a_pagar': lambda text: _to_float(_find_first_match(text, [
            r"TOTAL\s+A\s+PAGAR[:;]?\s*R?\s*([\d.,]+)",
            r"Valor.*?Pagar[:;]?\s*R?\s*([\d.,]+)",
            r"Total Consolidado[:;]?\s*R?\s*([\d.,]+)"
        ])),
        'discriminacao_fisco': lambda text: _find_first_match(text, [
            r"DISCRIMINAÇÃO DA OPERAÇÃO - RESERVADO AO FISCO[\s\S]*?(?=Total Consolidado|TOTAL)"
        ]),
        'total_consolidado': lambda text: _to_float(_find_first_match(text, [r"Total Consolidado[:;]?\s*R?\s*([\d.,]+)"])),
        'consumo': lambda text: _to_float(_find_first_match(text, [r'([\d,]+)\s+kWh']))
    },
    'telefone': {
        'valor': lambda text: _to_float(_find_first_match(text, [
            r"Total\s+a\s+pagar\s+R\[\s*([\d.,]+)",
            r"Valor[\s\n]*cobrado/total[\s\n]*a[\s\n]*pagar[:;]?\s*R?\s*([\d.,]+)",
            r"Total[\s\n]*final[\s\n]*da[\s\n]*fatura[:;]?\s*R?\s*([\d.,]+)",
            r"Valor[\s\n]*do[\s\n]*documento(?:\s|\n)*R?\s*([\d.,]+)",
            r"Total[\s\n]*a[\s\n]*Pagar[:;]?\s*R?\s*([\d.,]+)",
            r"VALOR[\s\n]*TOTAL[:;]?\s*R?\s*([\d.,]+)"
        ])),
        'data_vencimento': lambda text: _to_iso_date(_find_first_match(text, [
            r"VENCIMENTO\s+TOTAL\s+DA\s+FATURA\s*.*?(\d{2}[\s./-]\d{2}[\s./-]\d{4})",
            r"Data\s+de\s+vencimento[:;]?\s*(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})",
            r"Vencimento[:;]?\s*(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})"
        ])),
        'data_emissao': lambda text: _to_iso_date(_find_first_match(text, [
            r"Data\s+da\s+emissão[:;]?\s*(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})",
            r"Data\s+d[ae]\s+Emissão[:;]?\s*(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})",
            r"EMISSÃO(?:\s|\n)*(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})"
        ])),
        'numero': lambda text: _find_first_match(text, [
            r"TELEFONE_FIXO_SALTO_CONTRATO_ADM_\d+/\d+[\s\n]*nº[\s\n]*(\d+)",
            r'(?:Telefone|N[ú|º]mero):[\s\n]*([\d\s\(\) -]+)'
        ]),
        'numero_nota_fiscal': lambda text: _find_first_match(text, [
            r"Nota[\s\n]*Fiscal[\s\n]*\(NFST\)[:;]?[\s\n]*([\d.]+)",
            r"Nº[\s\n]*DA[\s\n]*NOTA[\s\n]*FISCAL[:;]?[\s\n]*([\d.]+)",
            r"Número[\s\n]*da[\s\n]*Nota[\s\n]*Fiscal[\s\n]*\(NFST\)[\s\n]*→[\s\n]*([\d.]+)"
        ]),
        'numero_fatura': lambda text: _find_first_match(text, [
            r"FATURA[\s\n]*Nº[:;]?[\s\n]*(\d+)",
            r"Número[\s\n]*da[\s\n]*Fatura[\s\n]*→[\s\n]*.*?(\d+)"
        ]),
        'cnpj_prestador': lambda text: _find_first_match(text, [
            r"CNPJ[\s\n]*do[\s\n]*prestador(?:[\s\n]|\(beneficiário\))*([\d./-]+)",
            r"CNPJ:?[\s\n]*(\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2})"
        ]),
        'cnpj_tomador': lambda text: _find_first_match(text, [
            r"CNPJ[\s\n]*do[\s\n]*tomador(?:[\s\n]|\(pagador/cliente\))*([\d./-]+)",
            r"CPF/CNPJ[\s\n]*:?[\s\n]*(\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2})"
        ]),
        'base_calculo_icms': lambda text: _to_float(_find_first_match(text, [
            r"Base\s+de\s+Cálculo\s+ICMS(?:\s|\n)*R?\s*([\d.,]+)"
        ])),
        'valor_icms': lambda text: _to_float(_find_first_match(text, [
            r"ICMS[\s\n]*\(\d+%[:;]?\s*R?\s*([\d.,]+)"
        ])),
        'valor_pis': lambda text: _to_float(_find_first_match(text, [
            r"PIS[\s\n]*[\d.,]+%[:;]?\s*R?\s*([\d.,]+)"
        ])),
        'valor_cofins': lambda text: _to_float(_find_first_match(text, [
            r"COFINS[\s\n]*\(\d+%[:;]?\s*R?\s*([\d.,]+)"
        ]))
    },
    'internet': {
        'valor': lambda text: _to_float(_find_first_match(text, [ # Padrões mais genéricos e ordenados por prioridade
            r"Valor\s+Total\s+dos\s+Servi[cç]os[^\d]*?R?\s*\$\s*([\d.,]+)",
            r"TOTAL\s+A\s+PAGAR[^\d]*?R?\s*\$\s*([\d.,]+)",
            r"Valor\s+L[íi]quido\s+da\s+Fatura[^\d]*?R?\s*\$\s*([\d.,]+)",
            r"VALOR\s+TOTAL\s+DA\s+NOTA[^\d]*?R?\s*\$\s*([\d.,]+)",
            r"VALOR\s+A\s+PAGAR[^\d]*?R?\s*\$\s*([\d.,]+)",
            r"VALOR\s+TOTAL[^\d]*?R?\s*\$\s*([\d.,]+)",
        ])),
        'data_vencimento': lambda text: _to_iso_date(_find_first_match(text, [
            r"VENCIMENTO\s+DA\s+FATURA[^\d/]*?(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})",
            r"Data\s+d[eo]\s+Vencimento[^\d/]*?(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})",
            r"VENCIMENTO[^\d/]*?(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})",
            r"Vence\s+em[^\d/]*?(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})",
        ])),
        'data_emissao': lambda text: _to_iso_date(_find_first_match(text, [
            r"Data\s+d[ae]\s+Emiss[aã]o[^\d/]*?(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})",
            r"EMISSÃO(?:\s|\n)*(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})",
            r"Data\s+do\s+Documento[^\d/]*?(\d{2}[\s./-]\d{2}[\s./-]\d{4})",
        ])),
        'numero_nota_fiscal': lambda text: _find_first_match(text, [
            r"NF\s*(\d{3}\.\d{3}\.\d{3})",
            r"Nota\s+Fiscal\s+N[°ºo\s:]{,4}([\d\.-]+)",
            r"N[°ºo\s\.]{,4}da\s+Nota\s+Fiscal\s+[:\s]+([\d\.-]+)",
        ]),
        'periodo_prestacao': lambda text: _find_first_match(text, [
            r"Per[íi]odo\s+da\s+Presta[çc][ãa]o[^\n]*?(?:de\s+|De\s+)?([\d/]+\s+at[eé]\s+[\d/]+)",
            r"Per[ií]odo\s+de\s+apuração[^\n]*?([\d/]+\s+a\s+[\d/]+)",
        ]),
        'natureza_operacao': lambda text: _find_first_match(text, [
            r"Natureza\s+da\s+Opera[cç][aã]o[^\n]*?([^\n]+)",
        ]),
        'cfop': lambda text: _find_first_match(text, [
            r"CFOP[:;]?\s*(\d{4})",
        ]),
        'base_calculo_icms': lambda text: _to_float(_find_first_match(text, [
            r"Base\s+de\s+C[áa]lculo\s+do\s+ICMS[^\d]*?([\d,.-]+)",
        ])),
        'aliquota_icms': lambda text: _to_float(_find_first_match(text, [
            r"Al[íi]quota\s+ICMS[^\d]*?([\d,.-]+)",
        ])),
        'valor_icms': lambda text: _to_float(_find_first_match(text, [
            r"Valor\s+do\s+ICMS[^\d]*?([\d,.-]+)",
        ])),
        'cnpj_prestador': lambda text: _find_first_match(text, [
            r"Emitente[\s\S]*?CNPJ[^\d]*?(\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2})",
            r"Prestador\s+de\s+Servi[cç]o[\s\S]*?CNPJ[^\d]*?(\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2})",
        ]),
        'cnpj_tomador': lambda text: _find_first_match(text, [
            r"Destinat[áa]rio[\s\S]*?CNPJ[^\d]*?(\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2})",
            r"Tomador\s+de\s+Servi[cç]o[\s\S]*?CNPJ[^\d]*?(\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2})",
        ]),
    },
    'semparar': {
        'valor': lambda text: _to_float(_find_first_match(text, [
            r"Valor\s+L[íi]quido\s+da\s+Fatura[^\d]*?R?\s*([\d.,]+)",
            r"Valor\s+Líquido\s+a\s+Pagar[:;]?\s*R?\s*([\d.,]+)",
            r"Total\s+da\s+Nota\s+Fiscal[:;]?\s*R?\s*([\d.,]+)"
        ])),
        'data_vencimento': lambda text: _to_iso_date(_find_first_match(text, [
            r"Data\s+de\s+Vencimento[:;]?\s*(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})",
            r"VENCIMENTO[:;]?\s*(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})"
        ])),
        'data_emissao': lambda text: _to_iso_date(_find_first_match(text, [
            r"Data\s+d[ae]\s+Emissão[:;]?\s*(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})",
            r"EMISSÃO(?:\s|\n)*(\d{2}[\s./-]\d{2}[\s./-]\d{2,4})"
        ]))
    }
}

def extract_details(text: str, category: str) -> dict:
    """
    Extrai detalhes de um texto de fatura com base na sua categoria.

    Args:
        text: O texto completo da fatura.
        category: A categoria da fatura (ex: 'energia', 'agua').

    Returns:
        Um dicionário com os detalhes extraídos.
    """
    if category not in EXTRACTION_RULES:
        return {}

    rules = EXTRACTION_RULES[category]
    details = {}

    for field, extractor_func in rules.items():
        details[field] = extractor_func(text)

    return details
