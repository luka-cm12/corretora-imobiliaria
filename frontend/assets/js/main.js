document.addEventListener('DOMContentLoaded', () => {
  // Carregar imóveis em destaque
  loadFeaturedProperties();
  
  // Inicializar filtros
  if (document.querySelector('.property-filters')) {
    initFilters();
  }
  
  // Outras inicializações...
});

async function loadFeaturedProperties() {
  try {
    const response = await fetch('/api/properties?featured=true');
    const properties = await response.json();
    
    const container = document.getElementById('featuredProperties');
    container.innerHTML = '';
    
    properties.forEach(property => {
      const card = createPropertyCard(property);
      container.appendChild(card);
    });
    
  } catch (error) {
    console.error('Erro ao carregar imóveis:', error);
  }
}

function createPropertyCard(property) {
  const card = document.createElement('div');
  card.className = 'property-card';
  
  card.innerHTML = `
    <div class="property-image">
      <img src="${property.imagem_principal || '/assets/images/property-placeholder.jpg'}" alt="${property.titulo}">
    </div>
    <div class="property-details">
      <h3>${property.titulo}</h3>
      <p class="location">${property.bairro}, ${property.cidade}</p>
      <div class="property-features">
        <span>${property.quartos} <i class="material-icons">king_bed</i></span>
        <span>${property.banheiros} <i class="material-icons">bathtub</i></span>
        <span>${property.vagas_garagem} <i class="material-icons">directions_car</i></span>
        <span>${property.area_total}m²</span>
      </div>
      <p class="property-price">${formatCurrency(property.valor_venda || property.valor_aluguel)}</p>
      <a href="/imoveis/${property.id}" class="btn btn-sm">Ver Detalhes</a>
    </div>
  `;
  
  return card;
}

function formatCurrency(value) {
  if (!value) return 'Valor sob consulta';
  return new Intl.NumberFormat('pt-BR', { 
    style: 'currency', 
    currency: 'BRL' 
  }).format(value);
}

// Sistema de Filtros
function initFilters() {
  // Implementação dos filtros conforme mostrado anteriormente
  // ...
}