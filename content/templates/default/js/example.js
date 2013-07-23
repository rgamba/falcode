// First of all create the examples object
Mod.Example = {};

alert("example.js loaded first");

// This is the correct way to create functions for this module
Mod.Example.test = function(message){
    $("body").append(message);
}

$(function(){
    // jQuery functions can be called right away
    Mod.Example.test("<div>This was appended with the test function</div>");
});
