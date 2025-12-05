# 🚀 Guia de Hospedagem Gratuita - MentorHub

Este guia apresenta as melhores opções gratuitas para hospedar sua aplicação MentorHub.

## 📋 Requisitos da Aplicação

- ✅ **PHP 7.4+** (recomendado PHP 8.0+)
- ✅ **MySQL/MariaDB** (banco de dados)
- ✅ **Sessões PHP** (suporte a `session_start()`)
- ✅ **PDO MySQL** (extensão habilitada)
- ✅ **HTTPS/SSL** (recomendado)
- ⚠️ **Next.js** (opcional - pode ser hospedado separadamente)

---

## 🏆 Melhores Opções de Hospedagem Gratuita

### 1. **InfinityFree** ⭐ RECOMENDADO

**Por que escolher:**
- ✅ PHP 8.0+ suportado
- ✅ MySQL ilimitado
- ✅ Sem anúncios forçados
- ✅ SSL gratuito (Let's Encrypt)
- ✅ 5GB de espaço em disco
- ✅ Sem limite de tráfego
- ✅ Suporte a domínio personalizado
- ✅ Painel de controle (cPanel)

**Limitações:**
- ⚠️ CPU limitado (adequado para projetos pequenos/médios)
- ⚠️ Pode ter downtime ocasional

**Como usar:**
1. Acesse: https://www.infinityfree.net/
2. Crie uma conta gratuita
3. Escolha um subdomínio ou use seu domínio
4. Faça upload dos arquivos via FTP ou File Manager
5. Importe o banco de dados via phpMyAdmin

**Configuração do banco:**
- Edite `config/database.php` com as credenciais fornecidas
- Importe o arquivo `database.sql` via phpMyAdmin

---

### 2. **000webhost** (Hostinger)

**Por que escolher:**
- ✅ PHP 8.0+ suportado
- ✅ MySQL gratuito
- ✅ SSL gratuito
- ✅ 300MB de espaço
- ✅ 3GB de largura de banda
- ✅ Sem anúncios
- ✅ Painel de controle moderno

**Limitações:**
- ⚠️ Espaço limitado (300MB)
- ⚠️ Largura de banda limitada (3GB/mês)
- ⚠️ Conta inativa é deletada após 30 dias sem login

**Como usar:**
1. Acesse: https://www.000webhost.com/
2. Crie uma conta
3. Escolha um subdomínio
4. Faça upload via File Manager ou FTP
5. Configure o banco de dados

---

### 3. **Freehostia**

**Por que escolher:**
- ✅ PHP 7.4+ suportado
- ✅ MySQL incluído
- ✅ 250MB de espaço
- ✅ 6GB de largura de banda
- ✅ SSL gratuito
- ✅ Painel de controle

**Limitações:**
- ⚠️ Espaço limitado (250MB)
- ⚠️ Largura de banda limitada

**Como usar:**
1. Acesse: https://www.freehostia.com/
2. Escolha o plano "Chocolate" (gratuito)
3. Configure seu site

---

### 4. **AwardSpace**

**Por que escolher:**
- ✅ PHP 8.0+ suportado
- ✅ MySQL gratuito
- ✅ 1GB de espaço
- ✅ 5GB de largura de banda
- ✅ SSL gratuito
- ✅ Sem anúncios

**Limitações:**
- ⚠️ Largura de banda limitada (5GB/mês)

**Como usar:**
1. Acesse: https://www.awardspace.com/
2. Crie uma conta gratuita
3. Configure seu site

---

### 5. **Vercel** (Para Next.js)

**Se você quiser hospedar a parte Next.js separadamente:**

- ✅ Hospedagem gratuita ilimitada
- ✅ Deploy automático via Git
- ✅ SSL automático
- ✅ CDN global
- ✅ Domínio personalizado

**Como usar:**
1. Acesse: https://vercel.com/
2. Conecte seu repositório GitHub/GitLab
3. Deploy automático

**Nota:** A parte PHP ainda precisará ser hospedada em outro serviço.

---

## 📝 Passo a Passo - Deploy no InfinityFree (Recomendado)

### 1. Preparar os Arquivos

Antes de fazer upload, você precisa:

**a) Atualizar `config/database.php`:**

```php
<?php
// Substitua pelos dados fornecidos pelo InfinityFree
define('DB_HOST', 'sqlXXX.epizy.com'); // Host fornecido
define('DB_NAME', 'epiz_XXXXX_mentorias'); // Nome do banco
define('DB_USER', 'epiz_XXXXX'); // Usuário fornecido
define('DB_PASS', 'sua_senha'); // Senha fornecida
define('DB_CHARSET', 'utf8mb4');
// ... resto do código
```

**b) Verificar caminhos relativos:**
- Todos os caminhos devem ser relativos (já estão corretos no seu projeto)
- Exemplo: `assets/css/style.css` ✅

### 2. Criar Conta no InfinityFree

1. Acesse: https://www.infinityfree.net/
2. Clique em "Sign Up"
3. Preencha o formulário
4. Confirme o email

### 3. Criar Site

1. No painel, clique em "Create Account"
2. Escolha um subdomínio (ex: `mentorhub.epizy.com`)
3. Ou adicione seu domínio personalizado
4. Anote as credenciais do banco de dados

### 4. Upload dos Arquivos

**Opção A - Via File Manager (cPanel):**
1. Acesse o File Manager no painel
2. Navegue até `htdocs` ou `public_html`
3. Faça upload de todos os arquivos PHP
4. Mantenha a estrutura de pastas

**Opção B - Via FTP:**
1. Use um cliente FTP (FileZilla, WinSCP)
2. Conecte com as credenciais fornecidas
3. Faça upload dos arquivos

**Estrutura de pastas no servidor:**
```
public_html/
├── index.php
├── mentorias.php
├── carrinho.php
├── config/
│   └── database.php
├── includes/
│   ├── header.php
│   └── footer.php
├── api/
│   └── ...
├── assets/
│   ├── css/
│   └── js/
└── ...
```

### 5. Configurar Banco de Dados

1. Acesse phpMyAdmin no painel
2. Crie um novo banco de dados (ou use o fornecido)
3. Importe o arquivo `database.sql`
4. Atualize `config/database.php` com as credenciais

### 6. Testar

1. Acesse seu site: `https://seu-site.epizy.com`
2. Teste o cadastro de usuários
3. Teste o login
4. Verifique se o banco está funcionando

---

## 🔧 Configurações Importantes

### 1. Ativar SSL (HTTPS)

No InfinityFree:
1. Vá em "SSL Manager"
2. Clique em "Enable SSL"
3. Escolha "Let's Encrypt"
4. Aguarde alguns minutos

### 2. Configurar Domínio Personalizado (Opcional)

1. No painel, vá em "Domain Manager"
2. Adicione seu domínio
3. Configure os DNS apontando para o servidor
4. Aguarde propagação (24-48h)

### 3. Otimizações

**a) Comprimir arquivos CSS/JS:**
- Use ferramentas online para minificar
- Reduz tamanho dos arquivos

**b) Otimizar imagens:**
- Comprima imagens antes de fazer upload
- Use formatos WebP quando possível

**c) Cache:**
- Adicione headers de cache nos arquivos estáticos

---

## ⚠️ Limitações e Considerações

### Limitações Comuns em Hospedagem Gratuita:

1. **CPU/RAM Limitados:**
   - Pode ter lentidão com muitos usuários simultâneos
   - Otimize consultas ao banco de dados

2. **Espaço em Disco:**
   - Monitore o uso de espaço
   - Remova arquivos desnecessários

3. **Largura de Banda:**
   - Alguns serviços limitam tráfego mensal
   - Otimize imagens e assets

4. **Uptime:**
   - Pode ter downtime ocasional
   - Não recomendado para aplicações críticas

5. **Suporte:**
   - Suporte limitado ou via fórum
   - Sem garantias de SLA

---

## 🎯 Recomendações Finais

### Para Desenvolvimento/Testes:
✅ **InfinityFree** - Melhor opção geral

### Para Produção (quando crescer):
- **Hostinger** (R$ 9,90/mês) - Excelente custo-benefício
- **DigitalOcean** (US$ 5/mês) - Para mais controle
- **AWS Free Tier** - Para quem conhece cloud

### Estratégia Híbrida:
1. Hospede PHP no **InfinityFree**
2. Hospede Next.js no **Vercel** (gratuito)
3. Use API REST para comunicação entre eles

---

## 📚 Recursos Adicionais

- **Documentação InfinityFree:** https://forum.infinityfree.com/
- **Tutorial PHP Deployment:** https://www.php.net/manual/pt_BR/features.http-auth.php
- **MySQL Best Practices:** https://dev.mysql.com/doc/

---

## 🆘 Troubleshooting

### Erro de Conexão com Banco:
- Verifique credenciais em `config/database.php`
- Confirme que o banco foi criado
- Verifique se o host está correto

### Página em Branco:
- Ative exibição de erros temporariamente
- Verifique logs de erro no painel
- Confirme que todos os arquivos foram enviados

### Sessões não funcionam:
- Verifique permissões de escrita na pasta de sessões
- Confirme que `session_start()` está sendo chamado

### Arquivos não carregam (CSS/JS):
- Verifique caminhos relativos
- Confirme que os arquivos foram enviados
- Limpe cache do navegador

---

**Última atualização:** Dezembro 2024

**Dica:** Sempre faça backup do banco de dados regularmente!

