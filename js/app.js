function validateZipFileName(name)
{
    if(name)
    {
        if(name.toLowerCase().indexOf('zip') > name.length - 4)
        {
            return true;
        }
    }
    
    return false;
}