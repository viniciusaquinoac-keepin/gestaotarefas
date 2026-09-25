# Diretrizes de Design & Identidade Visual do Sistema

## 1. Proibição de Emojis
- **Regra Estrita**: É terminantemente proibido o uso de emojis (como 📞, 🎯, 🔍, 📊, 📄, 🏆, 🎉, ❌, ⚠️, ⏱️, etc.) na interface, títulos, menus, modais, selects e botões.
- **Objetivo**: Garantir uma identidade corporativa limpa, profissional e consistente.
- **Substituição**: Sempre utilizar ícones profissionais do pacote **Bootstrap Icons** (`<i class="bi bi-..."></i>`) ou texto claro e objetivo.

## 2. Padrão do Menu Lateral Esquerdo (Sidebar)
- Todos os itens de navegação da barra lateral devem seguir o padrão visual limpo com texto e ícones brancos/neutros herdados (`list-group-item-action bg-dark text-white`).
- Não utilizar classes de cores como `text-warning`, `text-info` ou `text-primary` diretamente nos ícones do menu lateral, mantendo uniformidade em toda a barra lateral.

## 3. Cores Semânticas
- Cores de destaque (como badges, bordas e botões) devem ser aplicadas apenas em elementos contextuais dentro das páginas (ex: badges de status, botões de ação e alertas), utilizando as classes utilitárias semânticas do Bootstrap 5 (`success`, `danger`, `warning`, `info`, `secondary`).
