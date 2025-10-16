import re
from datetime import datetime

def _to_float(valor_str: str) -> float | None:
    """Converte uma string de valor monetário (ex: '1.234,56') para float."""
    if not valor_str:
        return None
    try:
        # Remove pontos de milhar e substitui vírgula decimal por ponto
        valor_limpo = valor_str.replace('.', '').replace(',', '.')
        return float(valor_limpo)
    except (ValueError, TypeError):
        return None

def _to_iso_date(data_str: str) -> str | None:
    """Converte uma string de data (ex: '25/09/2025') para o formato ISO (YYYY-MM-DD)."""
    if not data_str:
        return None
    
    # Tenta formatos comuns de data
    for fmt in ('%d/%m/%Y', '%d/%m/%y'):
        try:
            return datetime.strptime(data_str, fmt).strftime('%Y-%m-%d')
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
            r"TOTAL[\s\n]*A[\s\n]*PAGAR(?:[\s\n]|\n)*R?\$?[\s\n]*([\d.,]+)",
            r"Valor[\s\n]*Total(?:[\s\n]|\n)*R?\$?[\s\n]*([\d.,]+)",
            r"TOTAL[\s\n]*DA[\s\n]*CONTA(?:[\s\n]|\n)*R?\$?[\s\n]*([\d.,]+)"
        ])),
        'data_vencimento': lambda text: _to_iso_date(_find_first_match(text, [
            r"Data[\s\n]*d[eo][\s\n]*Vencimento:?[\s\n]*(\d{2}/\d{2}/\d{2,4})",
            r"VENCIMENTO(?:[\s\n]|\n)*(\d{2}/\d{2}/\d{2,4})",
            r"Vence[\s\n]*em:?[\s\n]*(\d{2}/\d{2}/\d{2,4})"
        ])),
        'consumo': lambda text: _to_float(_find_first_match(text, [r'Consumo[\s\n]+m³[\s\n]+([\d,.]+)']))
    },
    'energia': {
        'valor': lambda text: _to_float(_find_first_match(text, [
            r"Total[\s\n]*Consolidado(?:[\s\n]|\n)*R?\$?[\s\n]*([\d.,]+)",
            r"Valor[\s\n]*Total[\s\n]*da[\s\n]*Operação(?:[\s\n]|\n)*R?\$?[\s\n]*([\d.,]+)",
            r"TOTAL[\s\n]*A[\s\n]*PAGAR(?:[\s\n]|\n)*R?\$?[\s\n]*([\d.,]+)",
            r"Valor.*?Pagar(?:[\s\n]|\n)*R?\$?[\s\n]*([\d.,]+)"
        ])),
        'data_vencimento': lambda text: _to_iso_date(_find_first_match(text, [
            r"Data[\s\n]*d[oa][\s\n]*Vencimento:?[\s\n]*(\d{2}/\d{2}/\d{2,4})",
            r"VENCIMENTO(?:[\s\n]|\n)*(\d{2}/\d{2}/\d{2,4})"
        ])),
        'consumo': lambda text: _to_float(_find_first_match(text, [r'Consumo[\s\n]+kWh[\s\n]+([\d,.]+)'])),
        'pn': lambda text: _find_first_match(text, [r"PN\s*(\d+)"]),
        'seu_codigo': lambda text: _find_first_match(text, [r"SEU CÓDIGO\s*(\d+)"]),
        'conta_mes': lambda text: _find_first_match(text, [r"CONTA MÊS\s*([\d/]+)"]),
        'discriminacao_fisco': lambda text: _find_first_match(text, [r"DISCRIMINAÇÃO DA OPERAÇÃO - RESERVADO AO FISCO\s*([\s\S]*?)(?=Total)"]),
        'total_consolidado': lambda text: _to_float(_find_first_match(text, [r"Total Consolidado\s*R?\$?\s*([\d.,]+)"]))
    },
    'telefone': {
        'valor': lambda text: _to_float(_find_first_match(text, [
            r"Total[\s\n]*a[\s\n]*Pagar(?:[\s\n]|\n)*R?\$?[\s\n]*([\d.,]+)",
            r"VALOR[\s\n]*TOTAL(?:[\s\n]|\n)*R?\$?[\s\n]*([\d.,]+)"
        ])),
        'data_vencimento': lambda text: _to_iso_date(_find_first_match(text, [
            r"Vencimento:?[\s\n]*(\d{2}/\d{2}/\d{2,4})"
        ])),
        'numero': lambda text: _find_first_match(text, [r'(?:Telefone|N[ú|º]mero):[\s\n]*([\d\s\(\) -]+)'])
    },
    'internet': {
        'valor': lambda text: _to_float(_find_first_match(text, [
            r"Total[\s\n]*a[\s\n]*Pagar(?:[\s\n]|\n)*R?\$?[\s\n]*([\d.,]+)",
            r"VALOR[\s\n]*TOTAL(?:[\s\n]|\n)*R?\$?[\s\n]*([\d.,]+)"
        ])),
        'data_vencimento': lambda text: _to_iso_date(_find_first_match(text, [
            r"Vencimento:?[\s\n]*(\d{2}/\d{2}/\d{2,4})",
            r"Vence[\s\n]*em:?[\s\n]*(\d{2}/\d{2}/\d{2,4})"
        ])),
        'provedor': lambda text: _find_first_match(text, [r'(?:Provedor|Empresa):[\s\n]*([^\n\r]+)']),
        'velocidade': lambda text: _find_first_match(text, [r'(?:Velocidade|Plano):[\s\n]*([\d]+\s*(?:Mbps|Gbps|Mega|Giga))'])
    },
    'semparar': {
        'valor': lambda text: _to_float(_find_first_match(text, [
            r"Valor[\s\n]*Líquido[\s\n]*a[\s\n]*Pagar(?:[\s\n]|\n)*R?\$?[\s\n]*([\d.,]+)",
            r"Total[\s\n]*da[\s\n]*Nota[\s\n]*Fiscal(?:[\s\n]|\n)*R?\$?[\s\n]*([\d.,]+)"
        ])),
        'data_vencimento': lambda text: _to_iso_date(_find_first_match(text, [
            r"Data[\s\n]*de[\s\n]*Vencimento:?[\s\n]*(\d{2}/\d{2}/\d{2,4})",
            r"VENCIMENTO(?:[\s\n]|\n)*(\d{2}/\d{2}/\d{2,4})"
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
