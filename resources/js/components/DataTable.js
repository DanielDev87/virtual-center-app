/**
 * DataTable Component for Virtual Center
 * Provides enhanced table functionality with sorting, filtering, and pagination
 */

class DataTable {
    constructor(selector, options = {}) {
        this.selector = selector;
        this.$table = $(selector);
        this.options = {
            searchable: true,
            sortable: true,
            pagination: true,
            pageSize: 10,
            ...options
        };
        
        this.data = [];
        this.filteredData = [];
        this.currentPage = 1;
        this.sortColumn = null;
        this.sortDirection = 'asc';
        this.searchTerm = '';
        
        this.init();
    }

    init() {
        if (this.$table.length === 0) {
            console.warn('DataTable: Table not found');
            return;
        }

        this.extractData();
        this.setupTable();
        this.setupControls();
        this.render();
    }

    extractData() {
        const $rows = this.$table.find('tbody tr');
        this.data = [];
        
        $rows.each((index, row) => {
            const $row = $(row);
            const rowData = {};
            
            $row.find('td').each((cellIndex, cell) => {
                const $cell = $(cell);
                const columnName = this.getColumnName(cellIndex);
                rowData[columnName] = $cell.text().trim();
            });
            
            this.data.push(rowData);
        });
        
        this.filteredData = [...this.data];
    }

    getColumnName(index) {
        const $header = this.$table.find('thead th').eq(index);
        return $header.data('column') || `column_${index}`;
    }

    setupTable() {
        // Add table classes
        this.$table.addClass('table table-striped table-hover');
        
        // Make headers sortable
        if (this.options.sortable) {
            this.$table.find('thead th').each((index, th) => {
                const $th = $(th);
                if (!$th.hasClass('no-sort')) {
                    $th.addClass('sortable').css('cursor', 'pointer');
                    $th.append(' <i class="fas fa-sort text-muted"></i>');
                }
            });
        }
    }

    setupControls() {
        // Create controls container
        const $controls = $(`
            <div class="datatable-controls mb-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                            <input type="text" class="form-control" placeholder="Buscar..." id="datatable-search">
                        </div>
                    </div>
                    <div class="col-md-6 text-end">
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="datatable-refresh">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="datatable-export">
                                <i class="fas fa-download"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `);
        
        this.$table.before($controls);
        
        // Setup event listeners
        this.setupEventListeners();
    }

    setupEventListeners() {
        // Search
        $('#datatable-search').on('keyup', this.debounce((e) => {
            this.searchTerm = e.target.value.toLowerCase();
            this.filterData();
            this.render();
        }, 300));

        // Sort
        this.$table.find('thead th.sortable').on('click', (e) => {
            const $th = $(e.currentTarget);
            const columnIndex = $th.index();
            const columnName = this.getColumnName(columnIndex);
            
            if (this.sortColumn === columnName) {
                this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
            } else {
                this.sortColumn = columnName;
                this.sortDirection = 'asc';
            }
            
            this.sortData();
            this.render();
        });

        // Refresh
        $('#datatable-refresh').on('click', () => {
            this.refresh();
        });

        // Export
        $('#datatable-export').on('click', () => {
            this.exportData();
        });
    }

    filterData() {
        if (!this.searchTerm) {
            this.filteredData = [...this.data];
            return;
        }

        this.filteredData = this.data.filter(row => {
            return Object.values(row).some(value => 
                value.toLowerCase().includes(this.searchTerm)
            );
        });
    }

    sortData() {
        if (!this.sortColumn) return;

        this.filteredData.sort((a, b) => {
            const aVal = a[this.sortColumn] || '';
            const bVal = b[this.sortColumn] || '';
            
            let comparison = 0;
            if (aVal > bVal) comparison = 1;
            if (aVal < bVal) comparison = -1;
            
            return this.sortDirection === 'asc' ? comparison : -comparison;
        });
    }

    render() {
        const $tbody = this.$table.find('tbody');
        $tbody.empty();

        // Calculate pagination
        const startIndex = (this.currentPage - 1) * this.options.pageSize;
        const endIndex = startIndex + this.options.pageSize;
        const pageData = this.filteredData.slice(startIndex, endIndex);

        // Render rows
        pageData.forEach(rowData => {
            const $row = $('<tr></tr>');
            
            Object.values(rowData).forEach(value => {
                $row.append(`<td>${value}</td>`);
            });
            
            $tbody.append($row);
        });

        // Update sort indicators
        this.updateSortIndicators();
        
        // Render pagination
        if (this.options.pagination) {
            this.renderPagination();
        }
    }

    updateSortIndicators() {
        this.$table.find('thead th i').removeClass('fa-sort-up fa-sort-down').addClass('fa-sort text-muted');
        
        if (this.sortColumn) {
            const columnIndex = this.$table.find('thead th').index(
                this.$table.find(`thead th[data-column="${this.sortColumn}"]`)
            );
            
            const $th = this.$table.find('thead th').eq(columnIndex);
            const $icon = $th.find('i');
            
            $icon.removeClass('fa-sort text-muted');
            $icon.addClass(this.sortDirection === 'asc' ? 'fa-sort-up text-primary' : 'fa-sort-down text-primary');
        }
    }

    renderPagination() {
        const totalPages = Math.ceil(this.filteredData.length / this.options.pageSize);
        
        if (totalPages <= 1) {
            $('.datatable-pagination').remove();
            return;
        }

        $('.datatable-pagination').remove();
        
        const $pagination = $(`
            <nav class="datatable-pagination mt-3">
                <ul class="pagination justify-content-center">
                    <li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                        <a class="page-link" href="#" data-page="prev">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                </ul>
            </nav>
        `);

        const $paginationList = $pagination.find('.pagination');
        
        // Add page numbers
        for (let i = 1; i <= totalPages; i++) {
            const $pageItem = $(`
                <li class="page-item ${i === this.currentPage ? 'active' : ''}">
                    <a class="page-link" href="#" data-page="${i}">${i}</a>
                </li>
            `);
            
            $paginationList.find('.page-item:last').before($pageItem);
        }

        // Add next button
        $paginationList.append(`
            <li class="page-item ${this.currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="#" data-page="next">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
        `);

        this.$table.after($pagination);

        // Add pagination event listeners
        $pagination.find('.page-link').on('click', (e) => {
            e.preventDefault();
            const page = $(e.currentTarget).data('page');
            
            if (page === 'prev' && this.currentPage > 1) {
                this.currentPage--;
            } else if (page === 'next' && this.currentPage < totalPages) {
                this.currentPage++;
            } else if (typeof page === 'number') {
                this.currentPage = page;
            }
            
            this.render();
        });
    }

    refresh() {
        this.extractData();
        this.filteredData = [...this.data];
        this.currentPage = 1;
        this.searchTerm = '';
        $('#datatable-search').val('');
        this.render();
    }

    exportData() {
        const csvContent = this.generateCSV();
        const blob = new Blob([csvContent], { type: 'text/csv' });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `datatable-export-${new Date().toISOString().split('T')[0]}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
    }

    generateCSV() {
        const headers = this.$table.find('thead th').map((index, th) => 
            $(th).text().trim()
        ).get();
        
        const rows = this.filteredData.map(row => 
            Object.values(row).map(value => `"${value}"`).join(',')
        );
        
        return [headers.join(','), ...rows].join('\n');
    }

    // Utility method
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Public methods
    updateData(newData) {
        this.data = newData;
        this.filteredData = [...this.data];
        this.render();
    }

    addRow(rowData) {
        this.data.push(rowData);
        this.filteredData = [...this.data];
        this.render();
    }

    removeRow(index) {
        this.data.splice(index, 1);
        this.filteredData = [...this.data];
        this.render();
    }

    getData() {
        return this.data;
    }

    getFilteredData() {
        return this.filteredData;
    }
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = DataTable;
}


