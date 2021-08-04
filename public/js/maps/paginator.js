define(['can/map'], function() {
    return can.Map.extend({
        define: {
            defaultPerPage: {
                value: 10,
                // constant
                set: function() {}
            },
            perPage: {
                value: 10
            },
            page: {
                value: 1
            },
            totalPages: {
                value: 0
            },
            totalItems: {
                value: 0,
                set: function(newTotal) {
                    if(newTotal != this.totalItems) {
                        this.attr('totalPages', Math.ceil(newTotal/this.perPage));
                        this.attr('page', 1);
                        return newTotal;
                    }
                }
            }
        },
        pageNumbers: function() {
            let maxVisiblePages = 7;
            // Show (at most) 7 page numbers, centered on the current page
            let firstVisible = this.attr('page');
            let lastVisible = this.attr('page');
            while(lastVisible - firstVisible < (maxVisiblePages - 1) && lastVisible - firstVisible < (this.attr('totalPages') - 1)) {
                firstVisible = Math.max(firstVisible - 1, 1);
                lastVisible = Math.min(lastVisible + 1, this.attr('totalPages'));
            }
            
            let pageNumbers = [];
            for(var p = firstVisible; p <= lastVisible; p++) {
                pageNumbers.push({
                    number: p,
                    current: p == this.attr('page')
                });
            }
            return pageNumbers;
        },
        changePage: function(newPage) {
            let currentPage = this.page;
            switch(newPage){
                case 'prev':
                    currentPage > 1 ? currentPage-- : 1;
                    break;
                case 'next':
                    currentPage < this.totalPages ? currentPage++ : this.totalPages;
                    break;
                case 'last':
                    currentPage = this.totalPages;
                    break;
                default:
                    currentPage = newPage;
                    break;
            }
            
            if(currentPage != this.page) {
                this.attr('page', currentPage);
            }
        },
        currentItems: function() {
            let firstRecord = (this.attr('page') - 1) * this.attr('perPage') + 1;
            let lastRecord = Math.min(this.attr('page') * this.attr('perPage'), this.attr('totalItems'));
            return "Displaying records " + firstRecord + " - " + lastRecord + " of " + this.attr('totalItems') + ".";
        },
        isVisible: function() {
            return this.attr('totalPages') > 1;
        },
        isCurrentPage: function(pageNumber) {
            return pageNumber == this.attr('page');
        },
        onFirstPage: function() {
            return this.attr('page') <= 1;
        },
        onLastPage: function() {
            return this.attr('page') >= this.attr('totalPages');
        }
    })
})