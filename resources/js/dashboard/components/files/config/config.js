export default {
  url: "/api/file/upload",
  method: 'post',
  maxFilesize: 16,
  maxFiles: 1,
  createImageThumbnails: false,
  acceptedFiles: '.pdf',
  headers: {
    'Authorization': 'Bearer ' + localStorage.getItem('token')
  }
}