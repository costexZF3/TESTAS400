/* 
 * 
 * CTP_functionsJS: Developed by Kristov Michelov
 * 01/22/2019 for CTP: COSTEX TRACTOR PARTS 
 *  
 * It's a Library with a bunch of tools developed in JS. It can be used through out whole project depending on functionalities you are implementing
 * * 
 *  1) convertToExcel (param1, param2 ) : this function convert the table with ID = table_toexcel inside your HTML to an
 *      Excel file.  
 *  - param1: Name of the Excel File
 *  - param1: Sheet Name inside the Excel File
 * 
 * 2) queryAllElement( element )
 *  - the function return a NodeList with all elements rendered on your Screen (HTML)
 *   - example: const allTR = queryallElement('tr');  return a NodeList with all Row in the HTML
 *   
 * 3) changeBackgrounColor (item, color)
 *    - change an Element(HTML) background color. (it can be <tr>, <td> etc.
  */

/*function to conver to EXCEL a table rendered */
/**
 * @param {string} fileName : Name will have the file exported to EXCEL
 * @param {string} sheetName : name wich you find the sheet inside de Excel File
 * @returns {undefined}
 */
const convertToExcel = (fileName, sheetName) =>{			   
                        var strDate = new Date().toISOString().replace(/[\-\:\.]/g, "_");				

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
                

//it return a NodeList with all element inside the document: (element can be all p, class, id, etc)
const queryAllElement = element => { return document.querySelectorAll(element);};

const changeBackgrounColor = (item, color) => { item.style.backgroundColor = color;};

/* function to hide an Element */
const hideElement = ( classElement )=> { $('.' + classElement).fadeOut();};

  /* function to show an Element */
const showElement = ( classElement )=> { $('.' + classElement).fadeIn();};