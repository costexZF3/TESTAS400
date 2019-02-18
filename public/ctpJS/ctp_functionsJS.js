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


const convertToExcel = (fileName, sheetName) =>{			   
//                       
                        var strDate = dateToStr();				

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
const hideElement = ( classElement )=> { $('.' + classElement).fadeOut();};

  /* function to show an Element */
const showElement = ( classElement )=> { $('.' + classElement).fadeIn();};

/* FilterTable: It's a function that calls to the API dataTable with and Object as parameter
 * this Object is sent with the initial values
 * 
 * @param {string} tableName 
 * @param {Object} initConfig
 * @returns {Object}
 */
const filterTable = ( tableName, initConfig )=> { $('.' + tableName ).dataTable( initConfig );};