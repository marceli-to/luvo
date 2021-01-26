export default {
  url: "/api/image/upload",
  method: 'post',
  maxFilesize: 8,
  maxFiles: 1,
  createImageThumbnails: false,
  acceptedFiles: '.png, .jpg',
  headers: {
    'Authorization': 'Bearer ' + localStorage.getItem('token')
  }
}