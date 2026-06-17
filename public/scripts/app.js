document.querySelector('.addSectionContainer').addEventListener('click', function() {
    var myModal = new bootstrap.Modal(document.getElementById('addSection'));
    myModal.show();
});

document.querySelectorAll('.addBlockContainer').forEach(function(element) {
    element.addEventListener('click', function() {
        var resId = this.getAttribute('data-res-id');
        var myModal = new bootstrap.Modal(document.getElementById('addBlock' + resId));
        myModal.show();
    });
});

document.querySelectorAll('.btnAddContents').forEach(function(element) {
    element.addEventListener('click', function() {
        var resId = this.getAttribute('data-res-id');
        var myModal = new bootstrap.Modal(document.getElementById('addContents' + resId)); 
        myModal.show();
    });
});