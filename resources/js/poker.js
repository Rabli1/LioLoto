import CryptoJS from "crypto-js";

const originalText = "Hello, World!";
const encryptedText = CryptoJS.AES.encrypt(originalText, window.gameSession.encryptKey).toString();
console.log(encryptedText)
const decryptedBytes = CryptoJS.AES.decrypt(encryptedText, window.gameSession.encryptKey);
const decryptedText = decryptedBytes.toString(CryptoJS.enc.Utf8);
console.log(decryptedText)
const playerSeats = $(".playerSeat"); 
const pot = $("#pot"); 