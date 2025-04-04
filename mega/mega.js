import { Storage } from 'megajs'
const [,, filePath, fileName] = process.argv; // Get command line arguments

const storage = new Storage({
  email: 'psychopath.epy@gmail.com',
  password: '42230822579@taraxia',
  userAgent: 'Ataraxia/1.0'
}).ready
storage.once('ready', () => {
    // User is now logged in
  console.log('ready');
  storage.upload(filePath, fileName,(error,file)=>{
    if(error)
        return console.error('There was an error: ', error);
    console.log('The file was uploaded!');
  })
})

storage.once('error', error => {
  // Some error happened
  console.error('Storage error: ', error);
})