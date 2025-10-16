import pandas as pd
import numpy as np
from sklearn.feature_extraction.text import TfidfVectorizer
from sklearn.linear_model import LogisticRegression
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.metrics import classification_report, accuracy_score, confusion_matrix
import joblib
import os
import logging
import re
import pypdf
import fitz  # PyMuPDF
import easyocr
from PIL import Image, ImageEnhance, ImageFilter
import cv2
from preprocessing import unified_preprocess_text

# Configuração de logging
logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

# --- OCR Reader ---
reader_ocr = None

def initialize_ocr_reader():
    """Inicializa o leitor OCR (EasyOCR) se ainda não foi inicializado."""
    global reader_ocr
    if reader_ocr is None:
        try:
            logger.info("Inicializando o leitor OCR (EasyOCR)...")
            reader_ocr = easyocr.Reader(['pt', 'en'], gpu=False)
            logger.info("Leitor OCR carregado com sucesso.")
        except Exception as e:
            logger.warning(f"AVISO: Não foi possível inicializar o leitor OCR (EasyOCR): {e}")

def preprocess_image_for_ocr(image: Image.Image) -> Image.Image:
    """Aplica pré-processamento a uma imagem para melhorar a qualidade do OCR."""
    
    # Converte para numpy array para uso com OpenCV
    img_np = np.array(image.convert('RGB'))
    
    # 1. Converte para escala de cinza
    gray = cv2.cvtColor(img_np, cv2.COLOR_RGB2GRAY)
    
    # 2. Redução de ruído com filtro bilateral, que preserva bordas
    denoised = cv2.bilateralFilter(gray, 9, 75, 75)
    
    # 3. Binarização com limiar adaptativo para lidar com diferentes iluminações
    # O valor 255 é o valor máximo a ser atribuído, ADAPTIVE_THRESH_GAUSSIAN_C é o método de limiarização,
    # THRESH_BINARY é o tipo de limiar, 11 é o tamanho da vizinhança, 2 é a constante C subtraída da média.
    binary_img = cv2.adaptiveThreshold(denoised, 255, cv2.ADAPTIVE_THRESH_GAUSSIAN_C, 
                                       cv2.THRESH_BINARY, 11, 2)

    return Image.fromarray(binary_img)

def extract_text_from_pdf(pdf_path: str) -> str:
    """Extrai texto de um arquivo PDF usando PyMuPDF, pypdf e OCR como fallback com pré-processamento."""
    full_text = ""
    try:
        # 1. Tenta com PyMuPDF (fitz)
        doc = fitz.open(pdf_path)
        for page in doc:
            full_text += page.get_text("text", sort=True) + "\n"
        doc.close()
        full_text = re.sub(r'\s+', ' ', full_text.strip())
        if len(full_text.strip()) > 50: # Verifica se o texto extraído é minimamente substancial
            logger.info(f"Texto extraído com sucesso de {os.path.basename(pdf_path)} usando PyMuPDF.")
            return full_text
        
        # 2. Fallback para pypdf
        logger.warning(f"PyMuPDF extraiu texto mínimo de {os.path.basename(pdf_path)}. Tentando pypdf.")
        reader = pypdf.PdfReader(pdf_path)
        full_text = ""
        for page in reader.pages:
            page_text = page.extract_text()
            if page_text:
                full_text += page_text + "\n"
        full_text = re.sub(r'\s+', ' ', full_text.strip())
        if len(full_text.strip()) > 50:
            logger.info(f"Texto extraído com sucesso de {os.path.basename(pdf_path)} usando pypdf.")
            return full_text

        # 3. Fallback para OCR com pré-processamento
        logger.warning(f"pypdf também extraiu texto mínimo de {os.path.basename(pdf_path)}. Tentando OCR com pré-processamento.")
        if reader_ocr is None:
            logger.error("Leitor OCR não está disponível para fallback.")
            return ""
            
        doc = fitz.open(pdf_path)
        ocr_text = ""
        for page_num in range(len(doc)):
            page = doc.load_page(page_num)
            pix = page.get_pixmap(dpi=300) # Aumenta a resolução para melhor qualidade do OCR
            img = Image.frombytes("RGB", [pix.width, pix.height], pix.samples)
            
            # Aplica o pré-processamento na imagem
            processed_img = preprocess_image_for_ocr(img)
            
            # Extrai texto da imagem processada
            result = reader_ocr.readtext(np.array(processed_img), detail=0, paragraph=True)
            
            if result:
                ocr_text += " ".join(result) + "\n"
        doc.close()
        
        if ocr_text.strip():
            logger.info(f"Texto extraído com sucesso de {os.path.basename(pdf_path)} usando OCR.")
        else:
            logger.warning(f"OCR não conseguiu extrair texto de {os.path.basename(pdf_path)}.")

        return ocr_text.strip()

    except Exception as e:
        logger.error(f"Falha ao processar o PDF {os.path.basename(pdf_path)}: {e}", exc_info=True)
        return ""

def build_dataset_from_pdfs(base_dir):
    """Constrói um DataFrame a partir de PDFs na pasta training_data."""
    training_data_path = os.path.join(base_dir, 'training_data')
    if not os.path.isdir(training_data_path):
        logger.error(f"Diretório de treinamento '{training_data_path}' não encontrado.")
        return None

    logger.info("=== Construindo Dataset a partir dos PDFs ===")
    data = []
    categories = [d for d in os.listdir(training_data_path) if os.path.isdir(os.path.join(training_data_path, d))]

    for category in categories:
        category_path = os.path.join(training_data_path, category)
        logger.info(f"Processando categoria: '{category}'")
        for filename in os.listdir(category_path):
            if filename.lower().endswith('.pdf'):
                pdf_path = os.path.join(category_path, filename)
                logger.info(f"  - Extraindo texto de: {filename}")
                text = extract_text_from_pdf(pdf_path)
                if text:
                    data.append({'text': text, 'category': category})
                else:
                    logger.warning(f"    - Texto não extraído de {filename}.")

    if not data:
        logger.error("Nenhum dado foi extraído dos PDFs. O dataset está vazio.")
        return None

    return pd.DataFrame(data)

def validate_dataset(df):
    """Valida o dataset e fornece estatísticas úteis."""
    logger.info("=== Validação do Dataset ===")
    
    logger.info(f"Total de amostras: {len(df)}")
    logger.info(f"Colunas presentes: {list(df.columns)}")
    
    null_counts = df.isnull().sum()
    if null_counts.any():
        logger.warning(f"Valores nulos encontrados:\n{null_counts}")
    
    class_counts = df['category'].value_counts()
    logger.info("Distribuição das classes:")
    for category, count in class_counts.items():
        percentage = (count / len(df)) * 100
        logger.info(f"  {category}: {count} amostras ({percentage:.1f}%)")
    
    min_samples = class_counts.min()
    max_samples = class_counts.max()
    
    if min_samples < 5: # Reduzido para 5 para permitir treinamento com menos dados iniciais
        logger.warning(f"ATENÇÃO: Alguma classe tem poucas amostras ({min_samples}). O modelo pode não generalizar bem. Considere coletar mais dados.")
    
    if max_samples / max(1, min_samples) > 10: # Aumentado para 10
        logger.warning(f"ATENÇÃO: Dataset pode estar desbalanceado. Razão max/min: {max_samples/max(1, min_samples):.1f}")
    
    if 'processed_text' in df.columns:
        text_lengths = df['processed_text'].str.len()
        logger.info(f"Comprimento do texto processado - Média: {text_lengths.mean():.1f}, Min: {text_lengths.min()}, Max: {text_lengths.max()}")
    
    return True

def create_vectorizer():
    """Cria um vetorizador TF-IDF. O pré-processamento já foi feito."""
    vectorizer = TfidfVectorizer(
        max_features=5000,
        min_df=2,
        max_df=0.8,
        ngram_range=(1, 2)
    )
    return vectorizer

def train_and_evaluate_model(X_train, X_test, y_train, y_test):
    """Treina o modelo e avalia sua performance."""
    
    logger.info("=== Treinamento do Modelo ===")
    
    classifier = LogisticRegression(
        random_state=42,
        max_iter=1000,
        C=1.0,
        class_weight='balanced'
    )
    
    classifier.fit(X_train, y_train)
    logger.info("Treinamento concluído.")
    
    cv_scores = cross_val_score(classifier, X_train, y_train, cv=5, scoring='accuracy')
    logger.info(f"Validação cruzada (5-fold) - Acurácia média: {cv_scores.mean():.3f} (±{cv_scores.std()*2:.3f})")
    
    y_pred = classifier.predict(X_test)
    test_accuracy = accuracy_score(y_test, y_pred)
    
    logger.info("=== Resultados no Conjunto de Teste ===")
    logger.info(f"Acurácia: {test_accuracy:.3f}")
    logger.info(f"\nRelatório de Classificação:\n{classification_report(y_test, y_pred)}")
    
    cm = confusion_matrix(y_test, y_pred)
    logger.info(f"Matriz de Confusão:\n{cm}")
    
    return classifier

def save_models(vectorizer, classifier, base_dir):
    """Salva o vetorizador e modelo de forma segura, com backup dos antigos."""
    import shutil
    vectorizer_path = os.path.join(base_dir, 'tfidf_vectorizer.pkl')
    model_path = os.path.join(base_dir, 'fatura_classifier_model.pkl')
    try:
        for path in [vectorizer_path, model_path]:
            if os.path.exists(path):
                backup_path = path + '.bak'
                shutil.copy2(path, backup_path)
                logger.info(f"Backup criado: {backup_path}")
        logger.info(f"Salvando o vetorizador em: {vectorizer_path}")
        joblib.dump(vectorizer, vectorizer_path)
        logger.info(f"Salvando o modelo em: {model_path}")
        joblib.dump(classifier, model_path)
        logger.info("Arquivos do modelo salvos com sucesso!")
        return True
    except Exception as e:
        logger.error(f"Erro ao salvar os modelos: {e}")
        return False

def test_saved_model(df, base_dir):
    """Testa o modelo salvo com uma amostra do dataset."""
    
    try:
        vectorizer_path = os.path.join(base_dir, 'tfidf_vectorizer.pkl')
        model_path = os.path.join(base_dir, 'fatura_classifier_model.pkl')
        
        loaded_vectorizer = joblib.load(vectorizer_path)
        loaded_model = joblib.load(model_path)
        
        logger.info("\n=== Teste do Modelo Salvo ===")
        for category in df['category'].unique():
            category_samples = df[df['category'] == category]
            if len(category_samples) > 0:
                sample = category_samples.sample(1).iloc[0]
                
                sample_text_processed = sample['processed_text']
                
                text_features = loaded_vectorizer.transform([sample_text_processed])
                prediction = loaded_model.predict(text_features)
                prediction_proba = loaded_model.predict_proba(text_features)
                confidence = max(prediction_proba[0])
                
                text_preview = sample['text'][:100].replace('\n', ' ')
                logger.info(f"Categoria Real: {sample['category']}")
                logger.info(f"Predição: {prediction[0]} (confiança: {confidence:.3f})")
                logger.info(f"Texto Original: '{text_preview}...'")
                logger.info("-" * 50)
        
        return True
        
    except Exception as e:
        logger.error(f"Erro no teste do modelo salvo: {e}")
        return False

def main():
    """Função principal do script de treinamento."""
    base_dir = os.path.dirname(__file__)
    
    # Inicializa o OCR
    initialize_ocr_reader()

    # 1. Constrói o dataset a partir dos PDFs
    df = build_dataset_from_pdfs(base_dir)
    if df is None or df.empty:
        logger.error("Falha ao construir o dataset. Abortando treinamento.")
        return False

    # Salva o dataset gerado para inspeção
    dataset_path = os.path.join(base_dir, 'faturas_dataset.csv')
    try:
        df.to_csv(dataset_path, index=False, encoding='utf-8')
        logger.info(f"Dataset gerado e salvo em: {dataset_path}")
    except Exception as e:
        logger.error(f"Não foi possível salvar o dataset em CSV: {e}")

    # 2. Pré-processamento do texto
    logger.info("Iniciando pré-processamento do texto...")
    df['processed_text'] = df['text'].apply(unified_preprocess_text)
    logger.info("Pré-processamento concluído.")

    validate_dataset(df)
    
    if len(df) < 10:
        logger.error("Dataset muito pequeno (< 10 amostras). Colete mais dados antes de treinar.")
        return False

    # 3. Divisão dos dados
    logger.info("\n=== Divisão dos Dados ===")
    try:
        X_train, X_test, y_train, y_test = train_test_split(
            df['processed_text'],
            df['category'],
            test_size=0.2,
            random_state=42,
            stratify=df['category']
        )
        logger.info(f"Treino: {len(X_train)} amostras, Teste: {len(X_test)} amostras")
    except ValueError as e:
        logger.warning(f"Não foi possível usar estratificação: {e}. Usando divisão normal.")
        X_train, X_test, y_train, y_test = train_test_split(
            df['processed_text'],
            df['category'],
            test_size=0.2,
            random_state=42
        )

    # 4. Vetorização dos Dados
    logger.info("\n=== Vetorização dos Dados ===")
    vectorizer = create_vectorizer()
    try:
        X_train_vec = vectorizer.fit_transform(X_train)
        X_test_vec = vectorizer.transform(X_test)
        logger.info(f"Vetorização concluída. Shape treino: {X_train_vec.shape}")
    except Exception as e:
        logger.error(f"Erro na vetorização: {e}")
        return False

    # 5. Treinamento e Avaliação
    classifier = train_and_evaluate_model(X_train_vec, X_test_vec, y_train, y_test)

    # 6. Salvamento dos Modelos
    if not save_models(vectorizer, classifier, base_dir):
        return False

    # 7. Teste do Modelo Salvo
    test_saved_model(df, base_dir)
    logger.info("\n=== Treinamento Concluído com Sucesso! ===")
    return True

if __name__ == '__main__':
    success = main()
    if not success:
        exit(1)