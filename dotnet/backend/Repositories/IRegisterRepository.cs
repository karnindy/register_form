using backend.Models;

namespace backend.Repositories
{
    public interface IRegisterRepository
    {
        Task<Register?> GetByIdAsync(int id);
        Task<IEnumerable<Register>> GetAllAsync();
        Task<int> CreateAsync(Register register);
        Task UpdateAsync(Register register);
        Task DeleteAsync(int id);
        
        // This interface makes it easy to swap implementation later
        // e.g., from EF Core (MySQL) to HttpClient (SQL Server Data API builder)
    }
}
