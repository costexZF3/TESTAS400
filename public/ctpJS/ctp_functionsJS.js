/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
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