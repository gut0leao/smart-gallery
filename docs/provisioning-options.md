# Workflow de Provisionamento Completo - Planejamento

## Opções de Implementação:

### Opção 1: Workflow Único (Completo)
- Provisiona VM + Instala WordPress + Deploy Plugin
- Mais complexo, mas tudo automatizado
- Ideal para ambientes temporários/demo

### Opção 2: Workflows Separados  
- Workflow 1: Provisiona VM + WordPress (execução única)
- Workflow 2: Deploy Plugin (execução frequente)
- Mais modular e reutilizável

### Opção 3: Terraform + GitHub Actions
- Terraform para infraestrutura (VM + rede + DNS)
- GitHub Actions para aplicação (WordPress + Plugin)
- Mais profissional e versionado

## Perguntas para definir implementação:

1. **Frequência de uso:**
   - Provisionar VMs frequentemente (demo/teste)?
   - Ou uma vez só para produção?

2. **Gerenciamento de estado:**
   - VM permanente com deploys frequentes?
   - VMs temporárias (criar/usar/destruir)?

3. **Complexidade desejada:**
   - Simples (tudo em um workflow)?
   - Modular (separado por responsabilidade)?
   - Profissional (Terraform + Actions)?

4. **Recursos:**
   - Apenas free tier?
   - Que região preferir?
   - Configurações específicas?