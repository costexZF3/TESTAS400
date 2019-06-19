/* 
 * 
 * CTP_functionsJS: Developed by Kristov Michelov
 * 01/22/2019 for CTP: COSTEX TRACTOR PARTS 
 *  
 * It's a Library with a bunch of tools developed in JS. It can be used through out whole project depending on functionalities you are implementing
 * * 
 *  0) function takes the actual date and return it as a string type... 
 *  1) convertToExcel (param1, param2 ) : this function convert the table with ID = table_toexcel inside your HTML to an
 *      Excel file.  
 *  - param1: Name of the Excel File
 *  - param1: Sheet Name inside the Excel File
 * 
 * 2) queryAllElement( element )
 *  - the function return a NodeList with all elements rendered on your Screen (HTML)
 *   - example: const allTR = queryallElement('tr');  return a NodeList with all Row in the HTML
 *   
 * 3) changeBGColor (item, color)
 *    - change an Element(HTML) background color. (it can be <tr>, <td> etc.
 */

/* convert the current date to string */
const dateToStr =()=> { return new Date().toISOString().replace(/[\-\:\.]/g, "-"); };

/*function to conver to EXCEL a table rendered */
/*
 * @param {string} fileName : Name will have the file exported to EXCEL
 * @param {string} sheetName : name wich you find the sheet inside de Excel File
 * @returns {undefined}
 */


const convertToExcel = (fileName, sheetName) => {
                    const strDate = dateToStr();				

                    $("#table_toexcel").table2excel({
                            exclude: ".noExl",
                            name: fileName, //"LostSales",
                            filename: fileName+"_"+ strDate +".xls",					
                            sheetName:sheetName,
                            exclude_img: true,
                            exclude_links: true,
                            exclude_inputs: true
                    });	 //end of : table2excel		 
    }; 
                

 /*
     * this function crea a new array of Elements on the web
     * @param {List} classElement
     * @returns {[]}
     */
const createArrayFromNodeList = ( nodeList )=> {
    let myArrayFromNodeList = []; // empty at first  
      for (let i = 0; i < nodeList.length; i++) {
            myArrayFromNodeList.push(nodeList[i]); // ahhh, push it            
        }  
        
      return myArrayFromNodeList;
    };
    

//it return a NodeList with all element inside the document: (element can be all p, class, id, etc)
const queryAllElement = element => { return document.querySelectorAll(element);};

const changeBGColor = (item, color) => { item.style.backgroundColor = color;};

/* function to hide an Element */
const hideElement = ( classElement ) => { $('.' + classElement).fadeOut();};

  /* function to show an Element */
const showElement = ( classElement ) => { $('.' + classElement).fadeIn();};

/* FilterTable: It's a function that calls to the API dataTable with and Object as parameter
 * this Object is sent with the initial values
 * 
 * @param {string} tableName 
 * @param {Object} initConfig
 * @returns {Object}
 */
const filterTable = ( tableName, initConfig )=> { $('.' + tableName ).dataTable( initConfig );};


/**
 *  This method() returns a button Object (EXCEL, PDF, COPY)
 *  
 * @param {type} config
 * @returns {createBtn.btn}
 */
const createBtn = ( config ) => {    
    const strDate = dateToStr();  

    //destructuring
    const {type, title, hint } = config;

    const btn = {
              extend: type,   
              className: 'submit-btn', 
              title: title+'-('+strDate+')',
              titleAttr : hint
          };

          //if the button is an pdf add new features to the object
          if (type === 'copy') {
              btn.text= 'C<u>O</u>PY';
              btn.key=  {key:'o', altKey: true };
          } else if (type === 'pdf') {
              btn.orientation= 'landscape';  //it's portrait by default 
              btn.pageSize= 'TABLOID'; //this can be A3, A4, A5, LEGAL, LETTER or TABLOID  : all are STRINGS 
              btn.text= '<u>P</u>DF';
              btn.key=  {key:'p', altKey: true };
          } else {
              btn.text= 'E<u>X</u>CEL'; 
              btn.key=  {key:'x', altKey: true };
          }

      return btn;
};


/**
 * This method creates the INITIAL OBJECT with the datatable configuration
 * 
 * @param {array} dropDownCols
 * @param {object} buttons
 * @returns {dataTableConfig.ctp_functionsJSAnonym$1}
 */
const dataTableConfig = ( dropDownCols, buttons, $columns=[] ) => { 
    //using method
    const optEXCEL = { type: 'excel', title: 'EXCEL', hint: 'Convert to Excel'};
    const optPDF = { type: 'pdf', title: 'PDF', hint: 'Convert to PDF'};
    const optCOPY = { type: 'copy', title: 'COPY', hint: 'Copy to Click board'};

    
    let btn1 = []; 
    
    //destructuring 
    const { excel, pdf, copy } = buttons;
    
    const buttonExcel = (excel) ?  createBtn( optEXCEL ) : null;
    const buttonPdf   =   (pdf) ? createBtn( optPDF ) :  null;    
    const buttonCopy  =  (copy) ? createBtn( optCOPY ) :  null;    
    
    btn1.push( buttonExcel ); btn1.push( buttonPdf );     btn1.push( buttonCopy ); 
    const Btns = btn1.filter( (item) => { return item !== null;});
        
    return (
        { 
            
            "columnDefs": [
              { "visible": false, "targets": $columns }
            ],
               
            select: false, 
            "lengthMenu" :  [ 
                              [10, 25, 50, 100,  -1], 
                              [10, 25, 50, 100, "All"]
                            ],

           dom: '<"ctp-buttons"l Bfr<t>ip>',

               /* see: exportOptions Object. This is an object. It help you to set up all concerning to BUTTONS */
               buttons: Btns,
               
           initComplete: 
           function () {
                this.api().columns( dropDownCols ).every( function () { //[0,1, 8,10, 16, 18,19] : columns to apply 
                   var column = this;
                   var select = $('<select><option value=""></option></select>')
                       .appendTo( $(column.footer()).empty() )
                       .on( 'change', function () {
                           var val = $.fn.dataTable.util.escapeRegex(
                               $(this).val()
                           );
                           column.search( val ? '^'+val+'$' : '', true, false ).draw();
                       } );

                   column.data().unique().sort().each( function ( d, j ) {
                       select.append( '<option value="'+d+'">'+d+'</option>' );
                   });
                }); //end: every()  
                
                //
                this.api().on( 'select', function (e, dt, type, indexes ) {
                    if (type === 'row') {
                        indexes++;
//                         alert( indexes()+'row clicked' );
                        let checked = $('#checked'+indexes).prop("checked");
                        
                        $('#checked'+indexes).prop("checked", !checked);
                       
                    }
                    
                } );
           } //end property InitComplete                 

       }//END: initial configuration 
    );
};

/**
 * 
 * @param {object} buttons
 * @param {object} dropDownsInCols
 * @returns {renderDataTable.table|undefined}
 */
 
const renderDataTable = ( buttons, dropDownsInCols ) => {
    
    // taking off CLASS dt-button to the buttons EXCEL, PDF, AND COPY
    $("button.dt-button").removeClass("dt-button");

    // getting initial config
    const settings = dataTableConfig( dropDownsInCols, buttons );  

    let table = filterTable('table_filtered', settings ); 

    //removing dt-button class to each HTMLElement (button) Associates to 
    // a datatable
    $('button').removeClass('dt-button');  
    table.order([0,'asc']).draw();

    return table;
}; 