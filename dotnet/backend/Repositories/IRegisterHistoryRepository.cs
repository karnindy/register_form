using backend.Models;

namespace backend.Repositories
{
    public interface IRegisterHistoryRepository
    {
        Task<IEnumerable<RegisterHistory>> GetAllAsync();
        Task<IEnumerable<RegisterHistory>> GetByRegisterIdAsync(int registerId);
    }
}
