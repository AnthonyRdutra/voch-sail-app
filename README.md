## ⚙️ Instalação e Configuração

### Pré-requisitos
- [Docker + Docker Compose](https://www.docker.com/get-started)
- (Opcional) [Composer](https://getcomposer.org/)

### Passos
```bash
# 1. Clonar o projeto
git clone https://github.com/SEU_USUARIO/voch-sail-app.git
cd voch-sail-app

# 2. Configurar ambiente (por se tratar de um teste já o deixei configurado)
cp .env.example .env

# 3. Subir containers
./vendor/bin/sail up -d

# 4. Instalar dependências
./vendor/bin/sail composer install
./vendor/bin/sail npm install && ./vendor/bin/sail npm run build

# 5. Gerar chave e migrar banco
./vendor/bin/sail artisan key:generate

#6. Exportação/Geração assincrona de Excel
./vendor/bin/sail artisan work:queue
```

📍 Acesse: **http://localhost**


## Funcionalidades

### Cadastros
Gerenciamento completo de **Grupos**, **Bandeiras**, **Unidades** e **Colaboradores**, com:
- Edição inline e exclusão direta  
- Dropdowns automáticos de Foreign Keys

### Relatórios
- Exibição e edição reativa com Livewire  
- Exportação em Excel (`storage/app/exports/`)  
- Indicação visual de carregamento e status  

### Auditoria
- Histórico completo de ações do usuário  
- Registro automático de criação, edição e exclusão

## Exportação de Relatórios
Os relatórios são gerados em `storage/app/exports/` e podem ser baixados diretamente pela interface do sistema.


## 📜 Licença
Distribuído sob a licença **MIT**.
