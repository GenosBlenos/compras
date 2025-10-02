import os
import re
import logging
import fitz  # PyMuPDF
import pypdf
import easyocr
import numpy as np
from PIL import Image
import cv2
import pytesseract
from pdf2image import convert_from_path

# --- Configuração ---
logger = logging.getLogger(__name__)
# Adicione o caminho para o executável do Tesseract se não estiver no PATH do sistema
# Exemplo para Windows: 
# pytesseract.pytesseract.tesseract_cmd = r'C:\ Program Files\Tesseract-OCR\tesseract.exe'

# --- Inicialização de Leitores (Singleton) ---
EASYOCR_READER = None
EASYOCR_ERROR = None

def initialize_easyocr_reader():
    """Inicializa o leitor EasyOCR de forma segura, uma única vez."""
    global EASYOCR_READER, EASYOCR_ERROR
    if EASYOCR_READER is None and EASYOCR_ERROR is None:
        try:
            logger.info("Inicializando o leitor OCR (EasyOCR)... Pode levar um momento.")
            EASYOCR_READER = easyocr.Reader(['pt', 'en'], gpu=False)
            logger.info("EasyOCR carregado com sucesso.")
        except Exception as e:
            EASYOCR_ERROR = e
            logger.error(f"ERRO CRÍTICO AO INICIALIZAR EasyOCR: {e}")

def _preprocess_image_for_ocr(image: Image.Image) -> np.ndarray:
    """Aplica pré-processamento a uma imagem para melhorar a qualidade do OCR."""
    # Converte para formato OpenCV (numpy array) e escala de cinza
    img_cv = np.array(image.convert('L'))
    # Aplica um thresholding binário adaptativo para limpar a imagem
    # Isso ajuda a remover ruído de fundo e a destacar o texto
    processed_img = cv2.adaptiveThreshold(
        img_cv, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, cv2.THRESH_BINARY, 11, 2
    )
    return processed_img

def _run_easyocr(image: np.ndarray, filename: str) -> str:
    """Executa o EasyOCR em uma imagem pré-processada."""
    if not EASYOCR_READER:
        logger.warning(f"[{filename}] EasyOCR não está disponível, pulando a extração.")
        return ""
    try:
        result = EASYOCR_READER.readtext(image, detail=0, paragraph=True)
        return " ".join(result).strip()
    except Exception as e:
        logger.error(f"[{filename}] Falha ao executar EasyOCR: {e}")
        return ""

def _run_tesseract(image: np.ndarray, filename: str) -> str:
    """Executa o Tesseract em uma imagem pré-processada."""
    try:
        # lang='por' para português
        return pytesseract.image_to_string(image, lang='por').strip()
    except pytesseract.TesseractNotFoundError:
        logger.error(f"[{filename}] Executável do Tesseract não encontrado. Verifique a instalação e o PATH.")
        return ""
    except Exception as e:
        logger.error(f"[{filename}] Falha ao executar Tesseract: {e}")
        return ""

def extract_text_from_pdf(pdf_path: str, use_ocr=True) -> str:
    """Extrai texto de um PDF usando múltiplos métodos, incluindo OCR avançado."""
    filename = os.path.basename(pdf_path)
    
    # 1. Extração de texto nativo (PyMuPDF)
    try:
        logger.debug(f"[{filename}] Tentando extração de texto nativo com PyMuPDF.")
        with fitz.open(pdf_path) as doc:
            full_text = " ".join(page.get_text("text", sort=True) for page in doc)
        full_text = re.sub(r'\s+', ' ', full_text).strip()
        if len(full_text) > 50: # Um limiar para considerar o texto válido
            logger.info(f"[{filename}] Texto extraído com sucesso via PyMuPDF.")
            return full_text
        logger.warning(f"[{filename}] PyMuPDF extraiu texto mínimo. Prosseguindo para OCR.")
    except Exception as e:
        logger.warning(f"[{filename}] PyMuPDF falhou: {e}. Prosseguindo para OCR.")

    if not use_ocr:
        logger.info(f"[{filename}] Extração por OCR desabilitada.")
        return ""

    # 2. Extração via OCR (com pré-processamento e múltiplos motores)
    logger.info(f"[{filename}] Iniciando pipeline de OCR.")
    initialize_easyocr_reader() # Garante que o EasyOCR esteja pronto
    
    final_text_parts = []
    try:
        images = convert_from_path(pdf_path, dpi=300)
    except Exception as e:
        logger.error(f"[{filename}] Falha ao converter PDF para imagem com pdf2image: {e}")
        return "" # Retorna vazio se a conversão falhar

    for i, image in enumerate(images):
        page_num = i + 1
        logger.debug(f"[{filename}] Processando OCR da página {page_num}/{len(images)}.")
        
        processed_image = _preprocess_image_for_ocr(image)
        
        # Tentativa 1: EasyOCR
        easyocr_text = _run_easyocr(processed_image, filename)
        
        # Tentativa 2: Tesseract (como fallback)
        tesseract_text = _run_tesseract(processed_image, filename)
        
        # Escolhe o melhor resultado (o que tiver mais texto)
        if len(easyocr_text) > len(tesseract_text):
            logger.info(f"[{filename}] Página {page_num}: EasyOCR produziu o melhor resultado.")
            final_text_parts.append(easyocr_text)
        else:
            logger.info(f"[{filename}] Página {page_num}: Tesseract produziu o melhor resultado.")
            final_text_parts.append(tesseract_text)
            
    full_text = "\n".join(final_text_parts).strip()

    if not full_text:
        logger.error(f"[{filename}] Todas as tentativas de extração (incluindo OCR) falharam.")
    else:
        logger.info(f"[{filename}] Extração por OCR concluída com sucesso.")

    return full_text